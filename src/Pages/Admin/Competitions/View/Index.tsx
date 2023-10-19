
import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import useAxios from "@/hooks/useAxios";
import Details from "./Tabs/Details";
import Predictions from "./Tabs/Predictions";
import PageHeader from "@/components/PageHeader";
import AutoTabs from "@/components/AutoTabs";
import { CompetitionInterface } from "@/interfaces/CompetitionInterface";
import Standings from "./Tabs/Standings";
import Teams from "./Tabs/Teams";
import Sources from "./Tabs/Sources";
import { subscribe, unsubscribe } from "@/utils/events";
import { CollectionItemsInterface } from "@/interfaces/UncategorizedInterfaces";
import Matches from "./Tabs/Matches";

const Index = () => {
    const { id } = useParams<any>();
    const { get, loading, data } = useAxios();

    const [key, setKey] = useState<number>(0)
    const [record, setRecord] = useState<CompetitionInterface>()
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
            name: "Details",
            content: <Details record={record} modelDetails={modelDetails} />,
        },
        {
            name: "Standings",
            content: <Standings record={record} setKey={setKey} />,
        },
        {
            name: "Teams",
            content: <Teams record={record} />,
        },
        {
            name: "Matches",
            content: <Matches record={record} />,
        },
        {
            name: "Predictions",
            content: <Predictions record={record} />,
        },
        {
            name: "Sources",
            content: <Sources record={record} />,
        },

    ];

    return (
        <div className="mb-3">
            {
                !loading && record && <PageHeader title={record.country.name + ' - ' + record.name} listUrl="/admin/competitions" />
            }

            <AutoTabs tabs={tabs} />
        </div>
    );

};

export default Index;
