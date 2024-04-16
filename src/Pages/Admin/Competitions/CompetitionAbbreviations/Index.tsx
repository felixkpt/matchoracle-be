import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import useListSources from '@/hooks/apis/useListSources';

const Index = () => {

    const [modelDetails, setModelDetails] = useState({})
    const { competitions: list_sources } = useListSources()

    return (
        <div>
            <h3>Competition Abbreviations List</h3>
            <div>
                <div className='d-flex justify-content-end'>
                    <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Statuses">Create Competition Abbreviation</button>
                </div>
                <AutoTable
                    baseUri='/admin/competitions/competition-abbreviations'
                    columns={[
                        {
                            label: 'ID',
                            key: 'id',
                        },
                        {
                            label: 'Name',
                            key: 'name',
                        },
                        {
                            label: 'Competition',
                            key: 'competition.name',
                        },
                        { key: 'Created_by' },
                        {
                            label: 'Created At',
                            key: 'Created_at',
                        },
                        {
                            label: 'Action',
                            key: 'action',
                        },
                    ]}
                    getModelDetails={setModelDetails}
                    search={true}
                    tableId='competitionAbbreviationsTable'
                    list_sources={list_sources}
                />
            </div>
            {
                modelDetails && <><AutoModal id={`Statuses`} modelDetails={modelDetails} actionUrl='/admin/competitions/competition-abbreviations' list_sources={list_sources} /></>
            }
        </div>
    );
};

export default Index;