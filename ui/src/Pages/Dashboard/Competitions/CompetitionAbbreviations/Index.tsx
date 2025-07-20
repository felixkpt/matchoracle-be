import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import useListSources from '@/hooks/list-sources/useListSources';
import { ModelDetailsInterface } from '@/interfaces/UncategorizedInterfaces';

const Index = () => {

    const [modelDetails, setModelDetails] = useState<ModelDetailsInterface>()
    const { competitions: listSources } = useListSources()

    return (
        <div>
            <h3>Competition Abbreviations List</h3>
            <div>
                <div className='d-flex justify-content-end'>
                    <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Statuses">Create Competition Abbreviation</button>
                </div>
                <AutoTable
                    baseUri='/dashboard/competitions/competition-abbreviations'
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
                            label: 'Country',
                            key: 'country.name',
                        },
                        {
                            label: 'Competition',
                            key: 'competition.name',
                        },
                        { key: 'Created_by' },
                        {
                            label: 'Created At',
                            key: 'created_at',
                        },
                        {
                            label: 'Updated',
                            key: 'Updated_at',
                        },
                        {
                            label: 'Action',
                            key: 'action',
                        },
                    ]}
                    getModelDetails={setModelDetails}
                    search={true}
                    tableId='competitionAbbreviationsTable'
                    listSources={listSources}
                />
            </div>
            {
                modelDetails && <><AutoModal id={`Statuses`} modelDetails={modelDetails} actionUrl='/dashboard/competitions/competition-abbreviations' listSources={listSources} /></>
            }
        </div>
    );
};

export default Index;