// src/Pages/Dashboard/Dashboard/Index.tsx
import { config } from "@/utils/helpers";
import AdvancedDashboardStats from "./Includes/AdvancedDashboardStats";
import TopMainStats from "./Includes/TopMainStats";

const Index = () => {

  return (
    <div>
      <div className="doc-index page-wrapper">
            <h2 className="page-title">{config.name} Dashboard</h2>
            <div className="container-fluid">
              <div className="row">
                <div className="col-xl-9">
                  <TopMainStats />
                </div>
                <div className="col-xl-3">
        
                </div>
              </div>
            </div>
            <div className="container-fluid mt-4">
              <h4>More stats</h4>
              <AdvancedDashboardStats />
            </div>
          </div>
    </div>
  )
}

export default Index