import AutoModal from "@/components/AutoModal";
import AutoTable from "@/components/AutoTable";
import PageHeader from "@/components/PageHeader";
import useListSources from "@/hooks/apis/useListSources";
import { useState } from "react";

const Show = () => {
  const [modelDetails, setModelDetails] = useState({})

  const { competitions: list_sources } = useListSources()

  const columns = [
    {
      label: 'Flag',
      key: 'Flag',
    },
    { label: 'Name', key: 'name' },
    {
      label: 'Slug',
      key: 'slug',
    },
    { label: 'Continent', key: 'continent.name' },
    { label: 'Has Competitions', key: 'has_competitions' },
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
  ]

  return (
    <div>
      <PageHeader title={'Countries list'} action="button" actionText="Create Country" actionTargetId="AutoModal" permission='admin/countries' />
      <div>
        <AutoTable columns={columns} baseUri={'/admin/countries'} search={true} getModelDetails={setModelDetails} />
      </div>
      {
        modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl='/admin/countries' list_sources={list_sources} /></>
      }
    </div>
  );
};

export default Show;
