import App from "@/utils/App"
import useAxios from "@/hooks/useAxios";
import { useEffect, useState } from "react";
import { DashboardStatsInterface } from "@/interfaces/FootballInterface";
import AdvancedDashboardStats from "./Includes/AdvancedDashboardStats";
import TopMainStats from "./Includes/TopMainStats";

type Props = {};

const Index = (props: Props) => {
  const { get, loading, errors } = useAxios();
  const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

  useEffect(() => {
    getStats()
  }, [])

  async function getStats() {
    get(`admin/stats`).then((results: any) => {
      if (results) {
        setStats(results)
      }
    })
  }

  return (
    <div>
      <div className="page-container">
        <div className="main-content">
          <div className="doc-index page-wrapper">
            <h2 className="page-title">{App.name()} Dashboard</h2>
            <div className="container-fluid">
              <div className="row">
                <div className="col-9">
                  <TopMainStats stats={stats} />
                </div>
                <div className="col-3">
                  Lorem ipsum dolor sit amet consectetur adipisicing elit. Praesentium nesciunt, eveniet quisquam sint harum qui ut. Consequuntur rerum cum error corrupti id nesciunt, pariatur veritatis quod totam eligendi delectus nostrum!
                </div>
              </div>
            </div>
            <div className="container-fluid mt-4">
              <h4>More stats</h4>
              <AdvancedDashboardStats />
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Index