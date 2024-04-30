import AutoTabs from "@/components/Autos/AutoTabs";
import Roles from "./Roles/Index";
import Permissions from "./Permissions/Index";
import Routes from "./Roles/View/Routes";

export default function Index(): JSX.Element {

  const tabs = [
    {
      name: "Roles",
      link: "roles",
      content: <Roles />,
    },
    {
      name: "Permissions",
      link: "permissions",
      content: <Permissions />,
    },
     {
      name: "Routes",
      link: "routes",
      content: <Routes />,
    },
  ];

  return (
    <div className="mb-3">
      <AutoTabs tabs={tabs} active="roles" />
    </div>
  );
}
