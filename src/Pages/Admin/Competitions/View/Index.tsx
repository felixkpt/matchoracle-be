
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useAxios from "@/hooks/useAxios";
import Details from "./Tabs/Details";
import PastPredictions from "./Tabs/PastPredictions";
import UpcomingPredictions from "./Tabs/UpcomingPredictions";
import PageHeader from "@/components/PageHeader";
import AutoTabs from "@/components/AutoTabs";
import { CompetitionInterface, SeasonInterface } from "@/interfaces/FootballInterface";
import Standings from "./Tabs/Standings";
import Teams from "./Tabs/Teams";
import Statistics from "./Tabs/Statistics";
import Sources from "./Tabs/Sources";
import { subscribe, unsubscribe } from "@/utils/events";
import { CollectionItemsInterface } from "@/interfaces/UncategorizedInterfaces";
import UpcomingMatches from "./Tabs/UpcomingMatches";
import PastMatches from "./Tabs/PastMatches";
import Seasons from "./Tabs/Seasons";
import Loader from "@/components/Loader";
import Error404 from "@/Pages/ErrorPages/Error404";

const Index = () => {
    const { id } = useParams<any>();
    const { get, loading, data } = useAxios();

    const [key, setKey] = useState<number>(0)
    const [record, setRecord] = useState<CompetitionInterface>()
    const [seasons, setSeasons] = useState<SeasonInterface | null>(null);
    const [selectedSeason, setSelectedSeason] = useState<SeasonInterface | null>(null);

    const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()

    useEffect(() => {

        if (id) {
            getRecord()
        }

    }, [id, key])

    function getRecord() {
        get(`admin/competitions/view/${id}`).then((res) => {

            if (res) {
                const { data, ...others } = res
                if (data) {
                    setRecord(data)
                }
                setModelDetails(others)
            }
        })
    }

    useEffect(() => {
        getSeasons()
    }, [record])

    async function getSeasons() {
        if (record && !seasons) {
            const { data: fetchedOptions } = await get(`/admin/seasons?all=1&competition_id=${record.id}`);
            setSeasons(fetchedOptions)
        }
    }
    const recordUpdated = (event: CustomEvent<{ [key: string]: any }>) => {

        if (event.detail) {
            const detail = event.detail;
            if (detail.elementId === 'addSources') {
                getRecord()
            }
        }

    }

    useEffect(() => {

        subscribe('ajaxPostDone', recordUpdated as EventListener)

        return () => unsubscribe('recordUpdated', recordUpdated as EventListener)
    }, [])


    const tabs = [

        {
            name: "Standings",
            content: <Standings record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Teams",
            content: <Teams record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Past Matches",
            content: <PastMatches record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Upcoming Matches",
            content: <UpcomingMatches record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Past Predictions",
            content: <PastPredictions record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Upcoming Predictions",
            content: <UpcomingPredictions record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Statistics",
            content: <Statistics record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Seasons",
            content: <Seasons record={record} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setKey={setKey} />,
        },
        {
            name: "Details",
            content: <Details record={record} modelDetails={modelDetails} />,
        },
        {
            name: "Sources",
            content: <Sources record={record} />,
        },

    ];

    return (
        <div className="mb-3">
            {
                !loading ?

                    record ?
                        <div>
                            <PageHeader title={record.country.name + ' - ' + record.name} listUrl="/admin/competitions" />
                            <AutoTabs tabs={tabs} />
                        </div>
                        :
                        <Error404 />
                    :
                    <Loader />
            }

        </div>
    );

};

export default Index;
