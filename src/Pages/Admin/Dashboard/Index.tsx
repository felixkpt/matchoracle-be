import { config } from "@/utils/helpers";
import AdvancedDashboardStats from "./Includes/AdvancedDashboardStats";
import TopMainStats from "./Includes/TopMainStats";

const Index = () => {

  return (
    <div>
      <div className="page-container">
        <div className="main-content">
          <div className="doc-index page-wrapper">
            <h2 className="page-title">{config.name} Dashboard</h2>
            <div className="container-fluid">
              <div className="row">
                <div className="col-xl-9">
                  <TopMainStats />
                </div>
                <div className="col-xl-3">
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