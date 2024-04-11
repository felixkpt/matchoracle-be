import GeneralModal from "@/components/Modals/GeneralModal";
import PageHeader from "@/components/PageHeader";
import useListSources from "@/hooks/apis/useListSources";
import { DataInterface } from "@/interfaces/UncategorizedInterfaces";
import { subscribe, unsubscribe } from "@/utils/events";
import { useEffect, useState } from "react";
import AddSource from "@/components/AddSource";
import AutoModalBody from "@/components/AutoModalBody";
import CreateOrUpdateFromSource from "@/components/CreateOrUpdateFromSource";
import AutoTabs from "@/components/AutoTabs";
import All from "./Tabs/All";
import OddsEnabled from "./Tabs/OddsEnabled";

const Index = () => {

    const [modelDetails, setModelDetails] = useState({})
    const [key, setKey] = useState(0)
    const [record, setRecord] = useState<DataInterface>()
    const [record2, setRecord2] = useState<DataInterface>()


    const { competitions: list_sources } = useListSources()
    const [actionUrl, setActionUrl] = useState<string>('/admin/competitions')

    const columns = [
        { key: 'Logo' },
        { label: 'Name', key: 'name' },
        { key: 'country.name' },
        { key: 'code' },
        { key: 'type' },
        { key: 'season' },
        { key: 'seasons_fetched' },
        { key: 'standings_fetched' },
        { key: 'p_matches_fetched' },
        { key: 'u_matches_fetched' },
        { key: 'odds', column:'odds.' },
        { label: 'has_teams', key: 'Has_teams' },
        { label: 'priority_no', key: 'priority_number' },
        {
            label: 'Created At',
            key: 'Created_at',
        },
        {
            label: 'Status',
            key: 'Status',
        },
        {
            label: 'Action',
            key: 'action',
        },
    ];

    const addSources = (e: CustomEvent) => {

        if (e.detail) {
            const detail = e.detail

            if (detail.classList.includes('autotable-modal-add-sources')) {
                setRecord2(detail)
            }
        }
    }

    useEffect(() => {

        subscribe('prepareModalAction', addSources as EventListener)
        return () => unsubscribe('prepareModalAction', addSources as EventListener)
    }, [modelDetails])

    useEffect(() => {
        if (record2)
            document.getElementById('addSourceButton')?.click()

    }, [record2])

    const prepareEdit = async (event: CustomEvent<{ [key: string]: any }>) => {

        const detail = event?.detail
        if (detail && detail.modelDetails) {
            setModelDetails(detail.modelDetails)
            setRecord(detail.record)
            setActionUrl(detail.action)

        }

    }

    useEffect(() => {
        if (record) {
            document.getElementById("competitionModalTrigger")?.click()
        }
    }, [record])

    useEffect(() => {

        // Add event listener for the custom ajaxPost event
        const prepareEventListener: EventListener = (event) => {
            const customEvent = event as CustomEvent<{ [key: string]: any }>;
            if (customEvent.detail) {
                prepareEdit(customEvent)
            }
        };

        subscribe('prepareEditCustom', prepareEventListener);

        // Cleaning the event listener when the component unmounts
        return () => {
            unsubscribe('prepareEditCustom', prepareEventListener);
        };
    }, []);

    function toggleEvent(e: any) {
        const id = e.target?.id

        const form = document.querySelector('#competitionModal form')
        if (form) {
            const manual = form.querySelector('.submit-button')
            const source = form.querySelector('.from-source-submit-button')
            const competitionOrigin = form.querySelector('input[name="competition_origin"]') as HTMLInputElement | null;

            if (manual && source && competitionOrigin) {

                if (id === 'manual-tab') {
                    manual.setAttribute('type', 'submit')
                    source.setAttribute('type', 'button')
                    competitionOrigin.value = ('manual')
                } else if (id === 'from-source-tab') {
                    manual.setAttribute('type', 'button')
                    source.setAttribute('type', 'submit')
                    competitionOrigin.value = ('source')
                }
            }
        }

    }

    useEffect(() => {

        document.querySelector('#competitionTabs')?.addEventListener('click', toggleEvent)

        return () => document.removeEventListener('click', toggleEvent)


    }, [])

    const tabs = [
        {
            name: "All",
            content: <All columns={columns} setModelDetails={setModelDetails} />,
        },
        {
            name: "OddsEnabled",
            content: <OddsEnabled columns={columns} setModelDetails={setModelDetails} />,
        },
    ]

    return (

        <div>
            <PageHeader title={'Competitions list'} action="button" actionText="Create Competition" actionTargetId="competitionModal" permission='admin/competitions' setRecord={setRecord} />
            <div className="d-flex justify-content-end">
                <div className={`col-6 col-md-3 form-group d-flex justify-content-end`}>
                    
                </div>
            </div>
            <div>
                <AutoTabs key={key} tabs={tabs} />
            </div>
            {
                modelDetails &&
                <GeneralModal title={record ? 'Update Competition' : `Create Competition`} actionUrl={actionUrl} size={'modal-lg'} id={`competitionModal`} setKey={setKey}>
                    <input type="hidden" name="competition_origin" defaultValue={'manual'} />

                    <ul className="nav nav-tabs" id="competitionTabs" role="tablist">
                        <li className="nav-item" role="presentation">
                            <button className="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab" aria-controls="manual" aria-selected="true">Manual</button>
                        </li>
                        <li className="nav-item" role="presentation">
                            <button className="nav-link" id="from-source-tab" data-bs-toggle="tab" data-bs-target="#from-source" type="button" role="tab" aria-controls="from-source" aria-selected="false">From source</button>
                        </li>
                    </ul>
                    <div className="tab-content" id="competitionTabsContent" key={record?.id}>
                        <div className="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                            <div className="mt-4 pt-1 border-top">
                                <AutoModalBody modelDetails={modelDetails} list_sources={list_sources} record={record} setKey={setKey} />
                            </div>
                        </div>
                        <div className="tab-pane fade" id="from-source" role="tabpanel" aria-labelledby="from-source-tab">
                            <div className="mt-4 pt-1 border-top">
                                <CreateOrUpdateFromSource record={record} />
                            </div>
                        </div>
                    </div>
                </GeneralModal>
            }
            {
                record2 &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addSourceButton" data-bs-toggle="modal" data-bs-target="#addSource"></button>
                    <GeneralModal title={`Add source for ${record2.record.name || '#'}`} actionUrl={`${record2.action || '#'}`} size={'modal-lg'} id={`addSource`} setKey={setKey}>
                        <AddSource key={record2.record.id} record={record2.record} />
                    </GeneralModal>
                </>
            }

        </div>
    );
}

export default Index;
