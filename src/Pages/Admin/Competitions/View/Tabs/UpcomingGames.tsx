import { useEffect, useState } from "react";
import FetchControls from "@/components/FetchControls";
import { CompetitionInterface } from "@/interfaces/CompetitionInterface";

interface Props {
    record: CompetitionInterface | undefined
}

const UpcomingGames = ({ record }: Props) => {
    const competition = record


    useEffect(() => {

        if (competition && competition.teams)
            setLimitList(generateArray(competition.teams.length || 0, 0));

    }, [competition])


    const [res, setRes] = useState()
    const [message, setMessage] = useState('')
    const [limitList, setLimitList] = useState<number[]>([]);
    const [limit, setLimit] = useState<number>(0);

    const handleSubmit = (e: any) => {
        e.preventDefault()

        if (competition)
            request.post(`/competitions/competition/${competition.id}/fixtures`, { limit }).then(function (resp) {
                const { data } = resp

                if (data?.message)
                    setMessage(data.message)
                else {
                    setMessage('')
                    setRes((curr: Res[] | undefined) => curr ? [...curr, ...data.res] : data.res);
                    setCompetition(data.competition)
                }
            })
    }

    function generateArray(length: number, startAt: number) {
        return Array.from({ length: length }, (_, index) => startAt + index);
    }

    return (
        <div>
            <div>
                <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark p-2">
                    <div className="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                        <h3 className="font-medium text-black dark:text-white">Get competition fixtures</h3>
                    </div>
                    <div className="p-2">
                        <form action="#" onSubmit={handleSubmit}>
                            <div className="mb-4">
                                {competition && <FetchControls item={competition} limitList={limitList} setLimit={setLimit} />}
                            </div>
                        </form>
                        {message && <div>{message}</div>}

                        <div>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Corrupti incidunt voluptas quia necessitatibus quisquam obcaecati sed recusandae nostrum, magnam, beatae sint. Facere veniam non alias reprehenderit porro fugit sequi cupiditate.
                        </div>

                    </div>
                </div>

            </div>
        </div>
    );
};

export default UpcomingGames;
