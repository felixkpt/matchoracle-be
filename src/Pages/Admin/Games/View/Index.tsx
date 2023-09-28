import DefaultLayout from "@/Layouts/DefaultLayout";
import { useState } from "react";
import Details from "./Tabs/Details";
import Odds from "./Tabs/Odds";
import { Head, usePage } from "@inertiajs/react";
import { GetItem } from "@/interfaces";
import AutoTabs from "@/components/AutoTabs";

type Tab = {
  name: string;
  link: string;
  content: JSX.Element;
};

const Index: React.FC = () => {
  const { props } = usePage<{ game: GetItem }>();
  const { game } = props;

  const tabs: Tab[] = [
    { name: "Game Details", link: "details", content: <Details game={game} /> },
    { name: "Game Odds", link: "odds", content: <Odds game={game} /> },
  ];

  const [openTab, setOpenTab] = useState<string>('details');

  return (
    <DefaultLayout title={`${game.data?.home_team || 'A'} vs  ${game.data?.away_team || 'B'} | ${openTab}`}>
      <div className="container mx-auto">
        <AutoTabs tabs={tabs} currentTab={setOpenTab} />
      </div>
    </DefaultLayout>
  );
};

export default Index;
