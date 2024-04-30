import AutoModal from "@/components/Autos/AutoModal";
import AutoTable from "@/components/Autos/AutoTable";
import PageHeader from "@/components/PageHeader";
import { useState } from "react";

const Index = () => {

    const [modelDetails, setModelDetails] = useState({})

    const columns = [
        {
            label: 'Flag',
            key: 'Flag',
        },
        {
            label: 'Name',
            key: 'name',
        },
        {
            label: 'Slug',
            key: 'slug',
        },
        {
            label: 'Code',
            key: 'code',
        },
        {
            label: 'Priority NO',
            key: 'priority_number',
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

    return (
        <div>
            <PageHeader title={'Continents list'} action="button" actionText="Create Continent" actionTargetId="AutoModal" permission='dashboard/continents' />
            <div>
                <AutoTable columns={columns} baseUri={'dashboard/continents'} search={true} getModelDetails={setModelDetails} />
            </div>
            {
                modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl='/dashboard/continents' /></>
            }
        </div>
    );
};

export default Index;
