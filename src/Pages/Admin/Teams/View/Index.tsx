import PageHeader from "@/components/PageHeader"
import useAxios from "@/hooks/useAxios"
import { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import Details from "./Tabs/Details"
import Matches from "./Tabs/Matches"
import AutoTabs from "@/components/AutoTabs"
import { TeamInterface } from "@/interfaces/CompetitionInterface"
import { CollectionItemsInterface } from "@/interfaces/UncategorizedInterfaces"
import { subscribe, unsubscribe } from "@/utils/events"

type Props = {}

const Index = (props: Props) => {

  const { id } = useParams()
  const { get, loading } = useAxios()

  const [record, setRecord] = useState<TeamInterface>()
  const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()

  useEffect(() => {

    if (id) {
      getRecord()
    }
  }, [id])

  function getRecord() {
    get(`admin/teams/view/${id}`).then((res) => {

      if (res) {
        const { data, ...others } = res
        if (data) {
          setRecord(data)
        }
        setModelDetails(others)
      }
    })
  }

  const recordUpdated = (event: CustomEvent<{ [key: string]: any }>) => {

    if (event.detail) {
      const detail = event.detail;
      if (detail.elementId === 'addTeamSources') {
        getRecord()
      }
    }

  }

  useEffect(() => {

    subscribe('ajaxPostDone', recordUpdated as EventListener)

    return () => unsubscribe('recordUpdated', recordUpdated as EventListener)
  }, [])

  const tabs = [
    {
      name: "Details",
      content: <Details record={record} modelDetails={modelDetails} />,
    },
    {
      name: "Matches",
      content: <Matches record={record} />,
    },
  ];

  return (
    <div className="mb-3">
      {
        !loading && record && <PageHeader title={record.name} />
      }

      <AutoTabs tabs={tabs} />
    </div>
  )
}

export default Index