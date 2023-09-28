import AutoModal from "@/components/AutoModal";
import AutoTable from "@/components/AutoTable";
import AutoTableRecursive from "@/components/AutoTableRecursive";
import PageHeader from "@/components/PageHeader";
import { useState } from "react";

interface CountryInterface {
    id: string,
    name: string,
    slug: string,
    competitions: []

}

const Index = () => {

    const [modelDetails, setModelDetails] = useState({})

    const columns = [
        { label: 'Country', key: 'countries.name', isSorted: false, sortDirection: '' },
        { label: 'Popularity', key: 'countries.priority_no', isSorted: false, sortDirection: '' },
    ];

    return (
        <div>
            <PageHeader title={'Competitions list'} action="button" actionText="Create Competition" actionTargetId="AutoModal" permission='admin/competitions' />
            <div>
                <AutoTable columns={columns} baseUri={'admin/competitions/list'} search={true} getModelDetails={setModelDetails} />
            </div>
            {
                modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl='/admin/competitions' /></>
            }
        </div>
    );
};

export default Index;
