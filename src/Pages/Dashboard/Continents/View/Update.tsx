import { usePage } from "@inertiajs/react";
import DefaultLayout from "@/Layouts/DefaultLayout";
import request from "@/utils/request";
import { useEffect, useState } from "react";
import Nav from "./components/Nav";
import Checkbox from "@/components/Checkbox";

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

interface Team {
    id: string
    name: string
    action: string
}


interface Res {
    competition: {
        id: string
        name: string
        action: string
    }
    teams: [Team]
    removedTeams: [Team]
}

const Update = () => {
    const { props } = usePage<any>();

    const [competition, setCompetition] = useState<CompetitionInterface>()

    useEffect(() => {
        let { competition: compe } = props

        setCompetition(compe)
        setSource(compe.url)

    }, [props.competition])


    const [source, setSource] = useState('')
    const [has_teams, setIsDomestic] = useState(true)

    const [res, setRes] = useState<Res>()
    const [message, setMessage] = useState('')

    const handleSubmit = (e: any) => {
        e.preventDefault()

        if (competition)
            request.post(`/competitions/competition/${competition.id}`, { source, has_teams }).then(function (resp) {
                const { data } = resp

                if (data?.message)
                    setMessage(data.message)
                else {
                    setMessage('')
                    setRes(data)

                }
            })
    }

    return (
        <DefaultLayout>
            <div>
                <Nav title="Updates" competition={competition} setCompetition={setCompetition} />
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark p-2">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Update competition</h3>
                    </div>
                    <div className="p-6.5">
                        <form action="#" onSubmit={handleSubmit}>
                            <div className="mb-4">
                                <div className="mb-4.5">
                                    <label className="mb-2.5 block text-black dark:text-white">Competition url</label>
                                    <input value={source} onChange={(e) => setSource(e.target.value)} name="url" type="text" readOnly placeholder="Enter Competition url" className="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                                </div>
                                <div className="mb-4.5">
                                    <Checkbox checked={has_teams} message="Is Domestic?" onChange={(v: boolean) => setIsDomestic(v)} />
                                </div>
                                <button className="flex w-full justify-center rounded bg-primary p-3 font-medium text-gray">Fetch!</button>
                            </div>
                        </form>
                        {message && <div>{message}</div>}
                        {(!message && res) && <div className="rounded border bg-gray-100 shadow p-2">
                            <h4>Competition: {`${res.competition.name} (${res.competition.action})`}</h4>
                            <h5>Current Treams:</h5>
                            <ul className="ml-2 mb-2">
                                {res.teams.map((team, i) =>
                                    <li key={team.id}>{`${i + 1}. ${team.name} (${team.action})`} </li>
                                )}
                            </ul>
                            <h5>Removed Teams:</h5>
                            <ul className="ml-2 mb-2">
                                {res.removedTeams.map((team, i) =>
                                    <li key={team.id}>{`${i + 1}. ${team.name}`} </li>
                                )}
                            </ul>
                        </div>}
                    </div>
                </div>

            </div>
        </DefaultLayout>
    );
};

export default Update;
