import { Link, usePage } from "@inertiajs/react";
import DefaultLayout from "@/Layouts/DefaultLayout";
import request from "@/utils/request";
import { useEffect, useState } from "react";
import Nav from "./components/Nav";

interface CountryInterface {
    id: string;
    name: string;
    slug: string;
    competitions: [];

}

interface TeamInterface {
    id: string;
    name: string;
    slug: string;
}
interface CompetitionInterface {
    id: string;
    name: string;
    slug: string;
    teams: [TeamInterface];
    status: string;
}

const Show = () => {
    const { props } = usePage<any>();

    const [competition, setCompetition] = useState<CompetitionInterface>()

    useEffect(() => {
        let { competition: tmp } = props

        setCompetition(tmp)

    }, [props.competition])

    return (
        <DefaultLayout title={`${competition?.name} Teams`}>
            <div>
                <Nav competition={competition} setCompetition={setCompetition}/>

                {competition && competition.teams.map((team: TeamInterface) => (
                    <div className="ml-4" key={team.id}>
                        <Link href={`/teams/team/${team.id}`}>{team.name}</Link>
                    </div>
                ))}

            </div>
        </DefaultLayout>
    );
};

export default Show;
