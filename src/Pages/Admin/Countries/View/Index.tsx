import DefaultLayout from "@/Layouts/DefaultLayout";
import { useState } from "react";
import Details from "./Tabs/Details";
import Competitions from "./Tabs/Competitions";
import { Head, usePage } from "@inertiajs/react";
import { GetItem } from "@/interfaces";
import AutoTabs from "@/components/AutoTabs";

type Tab = {
  name: string;
  link: string;
  content: JSX.Element;
};

const Index: React.FC = () => {
  const { props } = usePage<{ country: GetItem }>();
  const { country } = props;

  const tabs: Tab[] = [
    { name: "Details", link: "details", content: <Details country={country} /> },
    { name: "Competitions", link: "competitions", content: <Competitions country={country} /> },
  ];

  const [openTab, setOpenTab] = useState<string>('details');

  return (
    <DefaultLayout title={`${country.data.name} | ${openTab}`}>
      <div className="container mx-auto">
        <AutoTabs tabs={tabs} currentTab={setOpenTab} />
      </div>
    </DefaultLayout>
  );
};

export default Index;
