import AutoTable from "@/components/AutoTable";
import DefaultLayout from "@/Layouts/DefaultLayout";
import { Head } from "@inertiajs/react";

const Show = () => {
  const baseUri = 'countries';
  const listUri = 'list';
  const search = true;
  const columns = [
    { label: 'id', key: 'id' },
    { label: 'Name', key: 'name' },
    { label: 'Has Competitions', key: 'has_competitions'},
    { label: 'priority_no', key: 'priority_no' },
    { label: 'status', key: 'status' },
    { label: 'created by', key: 'created_by', column: 'users.name' },
  ]

  return (
    <DefaultLayout title="Countries List">
      <AutoTable
        baseUri={baseUri}
        listUri={listUri}
        singleUri={`/countries/country`}
        search={search}
        columns={columns}
        action={{
          label: 'Actions',
          mode: 'buttons', // or 'dropdown'
          view: 'page',
          edit: 'modal',
        }}
      />
    </DefaultLayout>
  );
};

export default Show;
