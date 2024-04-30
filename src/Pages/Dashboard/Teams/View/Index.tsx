import { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import Details from "./Tabs/Details"
import Matches from "./Tabs/Matches"
import AutoTabs from "@/components/Autos/AutoTabs"
import { TeamInterface } from "@/interfaces/FootballInterface"
import { CollectionItemsInterface } from "@/interfaces/UncategorizedInterfaces"
import { subscribe, unsubscribe } from "@/utils/events"
import useAxios from "@/hooks/useAxios"
import TeamHeader from "./Includes/TeamHeader"
import Loader from "@/components/Loader"

type Props = {}

const Index = (props: Props) => {

  const { id } = useParams()
  const { get, loading } = useAxios()

  const [record, setRecord] = useState<TeamInterface>()
  const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()
  const [currentTab, setCurrentTabName] = useState<string | undefined>()

  useEffect(() => {

    if (id) {
      getRecord()
    }
  }, [id])

  function getRecord() {
    get(`dashboard/teams/view/${id}`).then((res) => {

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
      name: "Matches",
      content: <Matches record={record} />,
    },
    {
      name: "Details",
      content: <Details record={record} modelDetails={modelDetails} />,
    },

  ];

  return (
    <div className="mb-3">
      {
        !loading && record ?
          <div>
            <div>
              <div>
                <TeamHeader team={record} currentTab={currentTab} />
              </div>
            </div>
            <AutoTabs tabs={tabs} setCurrentTabName={setCurrentTabName} />
          </div>
          :
          <Loader />
      }

    </div>
  )
}

export default Index