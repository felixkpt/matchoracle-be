
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useAxios from "@/hooks/useAxios";
import Details from "./Tabs/Details";
import PastPredictions from "./Tabs/PastPredictions";
import UpcomingPredictions from "./Tabs/UpcomingPredictions";
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
        get(`admin/competitions/view/${id}`).then((res) => {

            if (res) {
                const { data, ...others } = res
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
            content: <Standings record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Teams",
            content: <Teams record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Past Matches",
            content: <PastMatches record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Upcoming Matches",
            content: <UpcomingMatches record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Past Predictions",
            content: <PastPredictions record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Upcoming Predictions",
            content: <UpcomingPredictions record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Odds",
            content: <Odds record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Statistics",
            content: <Statistics record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
        },
        {
            name: "Seasons",
            content: <Seasons record={record} selectedSeason={selectedSeason} mainKey={key} setMainKey={setMainKey} />,
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
                            <CompetitionHeader competition={record} currentTab={currentTab} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} setFromToDates={setFromToDates} setUseDates={setUseDates} />
                            <AutoTabs key={selectedSeason && selectedSeason.id} tabs={tabs} setCurrentTabName={setCurrentTabName} listUrl="/admin/competitions" countsUrl={`/admin/competitions/view/${record.id}/`} />
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
