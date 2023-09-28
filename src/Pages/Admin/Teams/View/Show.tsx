import AutoTable from "@/components/AutoTable";
import { TeamInterface } from "@/interfaces";
import DefaultLayout from "@/Layouts/DefaultLayout";
import { Head, usePage } from "@inertiajs/react";
import Nav from "./components/Nav";

const Show = () => {

  const { props } = usePage<any>()
  const { team }: { team: TeamInterface } = props

  const baseUri = 'teams/team/' + team.id;
  const listUri = 'get-games';

  const search = true;
  const columns = [
    { label: 'game_id', key: 'id' },
    { label: 'date_time', key: 'date_time' },
    { label: 'home_team', key: 'home_team' },
    { label: 'detailed', key: 'detailed' },
    { label: 'away_team', key: 'away_team' },
    { label: 'ht_results', key: 'ht_results' },
    { label: 'ft_results', key: 'ft_results' },
  ]

  return (
    <DefaultLayout title={`${team.name} details`}>
      <Nav title={''} team={team} setTeam={null} />
      <AutoTable
        baseUri={baseUri}
        listUri={listUri}
        singleUri="/games/game/{year}/{id}"
        search={search}
        columns={columns}
        action={{
          label: 'Actions',
          mode: 'dropdown', // or 'dropdown'
          view: 'page',
          edit: 'modal',
          delete: true,
        }}
      />

    </DefaultLayout>
  );
};

export default Show;
