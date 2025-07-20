import AutoTable from "@/components/Autos/AutoTable";
import GeneralModal from "@/components/Modals/GeneralModal";
import useListSources from "@/hooks/list-sources/useListSources";
import { DataInterface, ModelDetailsInterface } from "@/interfaces/UncategorizedInterfaces";
import { subscribe, unsubscribe } from "@/utils/events";
import { useEffect, useState } from "react";
import AutoModalBody from "@/components/Autos/AutoModalBody";
import CreateOrUpdateFromSource from "@/components/CreateOrUpdateFromSource";
import AddSource from "@/components/AddSource";
import { CompetitionTabInterface, SeasonsListInterface } from "@/interfaces/FootballInterface";
import UpdateCoach from "@/components/Teams/UpdateCoach";

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const Index: React.FC<Props> = ({ record, selectedSeason }) => {

    const competition = record

    const [modelDetails, setModelDetails] = useState<ModelDetailsInterface>()
    const [team, setTeam] = useState<DataInterface>()
    const [record2, setRecord2] = useState<DataInterface>()
    const [record3, setRecord3] = useState<DataInterface>()

    const { competitions: listSources } = useListSources()
    const [actionUrl, setActionUrl] = useState<string>('/dashboard/teams')

    const columns = [
        { key: 'logo' },
        { label: 'Name', key: 'name' },
        { label: 'Short Name', key: 'short_name' },
        { label: 'TLA', key: 'tla' },
        { label: 'Country', key: 'country.name' },
        { label: 'Priority Number', key: 'priority_number' },
        { label: 'Last Updated', key: 'last_updated' },
        { label: 'Status', key: 'Status' },
        { label: 'Updated', key: 'Updated_at' },
        { label: 'Action', key: 'action' },
    ];

    const addTeamSources = (e: CustomEvent) => {

        if (e.detail) {
            const detail = e.detail

            if (detail.classList.includes('autotable-modal-add-sources')) {
                setRecord2(detail)
            }
        }
    }
    useEffect(() => {

        subscribe('prepareModalAction', addTeamSources as EventListener)
        return () => unsubscribe('prepareModalAction', addTeamSources as EventListener)
    }, [modelDetails])

    useEffect(() => {
        if (record2)
            document.getElementById('addTeamSourceButton')?.click()

    }, [record2])


    const updateTeamCoach = (e: CustomEvent) => {

        if (e.detail) {
            const detail = e.detail

            if (detail.classList.includes('autotable-modal-update-coach')) {
                setRecord3(detail)
            }
        }
    }
    useEffect(() => {

        subscribe('prepareModalAction', updateTeamCoach as EventListener)
        return () => unsubscribe('prepareModalAction', updateTeamCoach as EventListener)
    }, [modelDetails])
    useEffect(() => {
        if (record3)
            document.getElementById('updateTeamCoachButton')?.click()

    }, [record3])


    const prepareEdit = async (event: CustomEvent<{ [key: string]: any }>) => {

        const detail = event?.detail
        if (detail && detail.modelDetails) {
            setModelDetails(detail.modelDetails)
            setTeam(detail.record)
            setActionUrl(detail.action)

        }

    }

    useEffect(() => {
        if (team) {
            document.getElementById("teamModalTrigger")?.click()
        }
    }, [team])

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

        const form = document.querySelector('#teamModal form')
        if (form) {
            const manual = form.querySelector('.submit-button')
            const source = form.querySelector('.from-source-submit-button')
            const competitionOrigin = form.querySelector('input[name="team_origin"]') as HTMLInputElement | null;

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

        document.querySelector('#teamTabs')?.addEventListener('click', toggleEvent)

        return () => document.removeEventListener('click', toggleEvent)


    }, [])

    return (

        <div>
           <div className="mt-1">
                {
                    competition &&
                    <AutoTable columns={columns} baseUri={`dashboard/competitions/view/${competition.id}/teams/${selectedSeason?.id || ''}`} search={true} getModelDetails={setModelDetails} listSources={listSources} tableId={'teamsTable'} customModalId="teamModal" />
                }

            </div>
            {
                modelDetails &&
                <GeneralModal title={team ? 'Update Team' : `Create Team`} actionUrl={actionUrl} size={'modal-lg'} id={`teamModal`}>
                    <input type="hidden" name="team_origin" defaultValue={'manual'} />

                    <ul className="nav nav-tabs" id="teamTabs" role="tablist">
                        <li className="nav-item" role="presentation">
                            <button className="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab" aria-controls="manual" aria-selected="true">Manual</button>
                        </li>
                        <li className="nav-item" role="presentation">
                            <button className="nav-link" id="from-source-tab" data-bs-toggle="tab" data-bs-target="#from-source" type="button" role="tab" aria-controls="from-source" aria-selected="false">From source</button>
                        </li>
                    </ul>
                    <div className="tab-content" id="teamTabsContent" key={team?.id}>
                        <div className="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                            <div className="mt-4 pt-1 border-top">
                                <AutoModalBody modelDetails={modelDetails} listSources={listSources} record={team} />
                            </div>
                        </div>
                        <div className="tab-pane fade" id="from-source" role="tabpanel" aria-labelledby="from-source-tab">
                            <div className="mt-4 pt-1 border-top">
                                <CreateOrUpdateFromSource record={team} />
                            </div>
                        </div>
                    </div>
                </GeneralModal>
            }
            {
                record2 &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="addTeamSourceButton" data-bs-toggle="modal" data-bs-target="#addTeamSource"></button>
                    <GeneralModal title={`Add source for ${record2.record.name || '#'}`} actionUrl={`${record2.action || '#'}`} size={'modal-lg'} id={`addTeamSource`}>
                        <AddSource key={record2.record.id} record={record2.record} />
                    </GeneralModal>
                </>
            }
            {
                record3 &&
                <>
                    <button type="button" className="btn btn-primary d-none" id="updateTeamCoachButton" data-bs-toggle="modal" data-bs-target="#updateTeamCoach"></button>
                    <GeneralModal title={`Update coach for ${record3.record.name || '#'}`} actionUrl={`${record3.action || '#'}`} id={`updateTeamCoach`}>
                        <div>
                            <UpdateCoach record={record3} />
                        </div>
                    </GeneralModal>
                </>
            }

        </div>
    );
}

export default Index;
