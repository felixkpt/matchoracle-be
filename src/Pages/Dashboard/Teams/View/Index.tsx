import { useEffect, useState } from "react"
import { useParams } from "react-router-dom"
import Details from "./Tabs/Details"
import Matches from "./Tabs/Matches"
import AutoTabs from "@/components/Autos/AutoTabs"
import { TeamInterface } from "@/interfaces/FootballInterface"
import { ModelDetailsInterface } from "@/interfaces/UncategorizedInterfaces"
import useAxios from "@/hooks/useAxios"
import TeamHeader from "./Includes/TeamHeader"
import Loader from "@/components/Loader"
import useAutoPostDone from "@/hooks/autos/useAutoPostDone"
import Predictions from "./Tabs/Predictions"

const Index = () => {

  const { id } = useParams()
  const { get, loading } = useAxios()

  const [record, setRecord] = useState<TeamInterface>()
  const [modelDetails, setModelDetails] = useState<ModelDetailsInterface>()
  const [currentTab, setCurrentTabName] = useState<string | undefined>()

  const { event } = useAutoPostDone()

  useEffect(() => {

    if (id) {
      getRecord()
    }
  }, [id])

  function getRecord() {
    get(`dashboard/teams/view/${id}`).then((response) => {

      if (response.results) {
        const { data, ...others } = response.results

        if (data) {
          setRecord(data)
        }
        setModelDetails(others)
      }
    })
  }

  useEffect(() => {

    if (event && event.id === 'addTeamSources') {
      getRecord()
    }

  }, [event])

  const tabs = [
    {
      name: "Matches",
      component: <Matches record={record} />,
    },
    {
      name: "Predictions",
      component: <Predictions record={record} />,
    },
    {
      name: "Details",
      component: <Details record={record} modelDetails={modelDetails} />,
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