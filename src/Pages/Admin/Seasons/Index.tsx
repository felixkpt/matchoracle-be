import AutoTable from '@/components/AutoTable';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';

const Seasons = () => {

    const { competitions: list_sources } = useListSources()

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
            <PageHeader title={'Seasons List'} action="button" actionText="Create season" actionTargetId="AutoModal" permission='admin.seasons' />
            <AutoTable
                baseUri='/admin/seasons'
                columns={columns}
                search={true}
                list_sources={list_sources}
            />
        </div>
    );
};

export default Seasons;

