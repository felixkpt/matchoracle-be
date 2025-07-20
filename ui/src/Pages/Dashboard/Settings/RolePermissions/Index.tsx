import AutoTabs from "@/components/Autos/AutoTabs";
import Roles from "./Roles/Index";
import Permissions from "./Permissions/Index";
import Routes from "./Roles/View/Routes";

export default function Index(): JSX.Element {

  const tabs = [
    {
      name: "Roles",
      link: "roles",
      component: <Roles />,
    },
    {
      name: "Permissions",
      link: "permissions",
      component: <Permissions />,
    },
     {
      name: "Routes",
      link: "routes",
      component: <Routes />,
    },
  ];

  return (
    <div className="mb-3">
      <AutoTabs tabs={tabs} active="roles" />
    </div>
  );
}
