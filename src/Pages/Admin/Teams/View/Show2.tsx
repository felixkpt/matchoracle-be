'use client';

import { usePage } from "@inertiajs/react";
import DefaultLayout from "@/Layouts/DefaultLayout";
import Nav from "./Includes/Nav";
import { useEffect, useState } from "react";
import { ResponseData, TeamInterface } from '@/interfaces';
import AutoTable from '@/components/AutoTable';
import AutoModal from "@/components/AutoModal";
import ViewModal from "@/components/ViewModal";
import PopUpModal from "@/components/PopUpModal";

const Show2 = () => {

    const { props } = usePage<any>();

    const [team, setTeam] = useState<TeamInterface>()

    useEffect(() => {
        let { team: tmp } = props
        setTeam(tmp)
    }, [props.team])

    const [viewModalOpen, setViewModalOpen] = useState<boolean | undefined>(undefined);
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [openModal, setOpenModal] = useState<string | undefined>();

    const [row, setSetRow] = useState<object | null>(null);

    const [reload, setReload] = useState<number>(0)

    const handleView = (row: object) => {
        setSetRow(row)
        setViewModalOpen(prev => undefined ? true : !prev)
    }

    const handleEdit = (row: object) => {
        setSetRow(row)
        setEditModalOpen(true)
    }

    const handleDelete = (row: object) => {
        setSetRow(row)
        setOpenModal('pop-up')
    }

    function response(response: ResponseData) {

        if (response.type == 'success') {
            setEditModalOpen(false)
            setReload(reload + 1)
        }
        else if (response.type == 'cancelled')
            setEditModalOpen(false)

    }

    function deleteResponse(response: ResponseData) {

        console.log(response)
        setOpenModal(undefined)
        setReload(reload + 1)

    }


    return (
        <DefaultLayout>

            <div>
                <Nav title="Games" team={team} setTeam={setTeam} />
            </div>

            <AutoTable
                baseUri='teams/team/01h438afn10zxntpsq839hs09n'
                listUri="get-games"
                columns={[
                    { label: 'id', key: 'id' },
                    { label: 'date_time', key: 'date_time' },
                    { label: 'home_team', key: 'home_team' },
                    { label: 'detailed', key: 'detailed' },
                    { label: 'away_team', key: 'away_team' },
                    { label: 'ht_results', key: 'ht_results' },
                    { label: 'ft_results', key: 'ft_results' },
                ]}
                action={{
                    label: '...', mode: 'dropdown',
                    view: true,
                    edit: true,
                    delete: true,
                }}

                handleView={handleView}
                handleEdit={handleEdit}
                handleDelete={handleDelete}
                reload={reload}
                search={true}
            />

            {
                editModalOpen &&
                <AutoModal
                    columns={
                        [
                            { label: 'HT results', key: 'ht_results' },
                            { label: 'FT results', key: 'ft_results' },
                            { label: 'Home Team', key: 'home_team' },
                            { label: 'Away Team', key: 'away_team' },
                            { label: 'Date Time', key: 'date_time' },
                        ]
                    }
                    row={row}
                    uri="teams/team/01h438afn10zxntpsq839hs09n/update"
                    method="post"
                    response={response}
                />
            }

            <ViewModal isOpen={viewModalOpen} row={row} size="3xl" />

            <PopUpModal uri='teams/team/01h438afn10zxntpsq839hs09n/update' method="POST" row={row} openModal={openModal} size="md" onClose={() => setOpenModal(undefined)} response={deleteResponse} />

        </DefaultLayout >
    );

};

export default Show2;
