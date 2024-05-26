
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useAxios from "@/hooks/useAxios";
import Details from "./Tabs/Details";
import PastPredictions from "./Tabs/PastPredictions";
import UpcomingPredictions from "./Tabs/UpcomingPredictions";
import AutoTabs from "@/components/Autos/AutoTabs";
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
import CompetitionHeader from "./Inlcudes/CompetitionHeader";
import FormatDate from "@/utils/FormatDate";
import Odds from "./Tabs/Odds";

const Index = () => {
    const { id } = useParams<any>();
    const { get, loading } = useAxios();

    const [key, setMainKey] = useState<number>(0)
    const [record, setRecord] = useState<CompetitionInterface>()
    const [seasons, setSeasons] = useState<SeasonInterface[] | null>(null);
    const [selectedSeason, setSelectedSeason] = useState<SeasonInterface | null>(null);
    const [currentTab, setCurrentTabName] = useState<string | undefined>()

    const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);
    const [useDate, setUseDates] = useState(false);

    const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()

    useEffect(() => {
        if (id) {
            getRecord()
        }
    }, [id])

    function getRecord() {
        get(`dashboard/competitions/view/${id}`).then((response) => {

            const results = response.results
            if (results) {
                const { data, ...others } = results
                if (data) {
                    setRecord(data)

                    const compe_seasons = data.seasons
                    if (compe_seasons.length > 0) {
                        setSeasons(compe_seasons)
                        setSelectedSeason(compe_seasons[0])
                    }
                }
                setModelDetails(others)
            }
        })
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

    useEffect(() => {

        if (selectedSeason && key) setMainKey(key + 1)

    }, [selectedSeason])

    const tabs = [

        {
            name: "Standings",
            component: <Standings record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Teams",
            component: <Teams record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Past Matches",
            component: <PastMatches record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Upcoming Matches",
            component: <UpcomingMatches record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Past Predictions",
            component: <PastPredictions record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Upcoming Predictions",
            component: <UpcomingPredictions record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Odds",
            component: <Odds record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Statistics",
            component: <Statistics record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Seasons",
            component: <Seasons record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Details",
            component: <Details record={record} modelDetails={modelDetails} />,
        },
        {
            name: "Sources",
            component: <Sources record={record} />,
        },

    ];

    return (
        <div className="mb-3">
            {
                !loading ?

                    record ?
                        <div>
                            <CompetitionHeader competition={record} currentTab={currentTab} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setFromToDates={setFromToDates} setUseDates={setUseDates} />
                            <AutoTabs key={selectedSeason && selectedSeason.id} tabs={tabs} setCurrentTabName={setCurrentTabName} listUrl="/dashboard/competitions" countsUrl={`/dashboard/competitions/view/${record.id}/tabs?season_id=${selectedSeason?.id}`} />
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
