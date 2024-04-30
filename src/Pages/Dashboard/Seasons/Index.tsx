import AutoTable from '@/components/Autos/AutoTable';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';

const Seasons = () => {

    const { competitions: listSources } = useListSources()

    const columns = [
        { key: 'competition.name' },
        { key: 'start_date' },
        { key: 'end_date' },
        { label: 'Matchday', key: 'current_matchday' },
        { key: 'Played' },
        { key: 'Winner' },
        { key: 'Created_by' },
        { label: 'Status', key: 'Status' },
        { label: 'Created At', key: 'Created_at' },
        { label: 'Action', key: 'action' },
    ]

    return (
        <div>
            <PageHeader title={'Seasons List'} action="button" actionText="Create season" actionTargetId="AutoModal" permission='dashboard.seasons' />
            <AutoTable
                baseUri='/dashboard/seasons'
                columns={columns}
                search={true}
                listSources={listSources}
                tableId='seasonsTable'
            />
        </div>
    );
};

export default Seasons;

