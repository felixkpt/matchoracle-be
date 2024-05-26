import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import Str from '@/utils/Str';
import useListSources from '@/hooks/list-sources/useListSources';
import AutoPageHeader from '@/components/Autos/AutoPageHeader';

const Index = () => {
    // begin component common config
    const pluralName = 'Users'
    const singularName = 'User'
    const uri = '/dashboard/settings/users'
    const componentId = Str.slug(pluralName)
    const [modelDetails, setModelDetails] = useState({})
    const search = true
    const columns = [
        {
            label: 'ID',
            key: 'id',
        },
        {
            label: 'User Name',
            key: 'name',
        },
        {
            label: 'Roles',
            key: 'Roles',
        },
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
    ]
    // end component common config

    const { tips: listSources } = useListSources()

    return (
        <div>
            <div>
                <AutoPageHeader pluralName={pluralName} singularName={singularName} componentId={componentId} />
                <AutoTable
                    baseUri={uri}
                    columns={columns}
                    getModelDetails={setModelDetails}
                    search={search}
                    tableId={`${componentId}Table`}
                    listSources={listSources}
                />
            </div>
            {
                modelDetails && <><AutoModal id={`${componentId}Modal`} modelDetails={modelDetails} actionUrl={uri} listSources={listSources} /></>
            }
        </div>
    );
};

export default Index;