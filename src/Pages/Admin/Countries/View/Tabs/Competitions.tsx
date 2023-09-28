import { GetItem } from '@/interfaces'

type Props = {
    country: GetItem
}

import AutoTable from '@/components/AutoTable';

const Competitions = ({ country }: Props) => {

    const baseUri = '/countries/country/' + country.data.id;
    const listUri = 'list-competitions';
    const search = true;
    const columns = [
        { label: 'id', key: 'id' },
        { label: 'Name', key: 'name' },
        { label: 'last_fetch', key: 'last_fetch' },
        { label: 'last_detailed_fetch', key: 'last_detailed_fetch' },
        { label: 'priority_no', key: 'priority_no' },
        { label: 'status', key: 'status' },
        { label: 'created by', key: 'created_by', column: 'users.name' },
    ]

    return (
        <div>
            <AutoTable
                baseUri={baseUri}
                listUri={listUri}
                singleUri={'/competitions/competition/{id}'}
                search={search}
                columns={columns}
                action={{
                    label: 'Actions',
                    mode: 'buttons', // or 'dropdown'
                    view: 'page',
                    edit: 'modal',
                }}
            />
        </div>
    );
};

export default Competitions;
