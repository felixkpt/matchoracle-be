import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';
import useListSources from '@/hooks/list-sources/useListSources';

const Seasons = () => {
    // begin component common config
    const pluralName = 'Seasons'
    const singularName = 'Season'
    const uri = '/dashboard/seasons'
    const componentId = Str.slug(pluralName)
    const search = true
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
    // end component common config
    const { competitions: listSources } = useListSources()

    return <AutoPage pluralName={pluralName} singularName={singularName} uri={uri} columns={columns} componentId={componentId} search={search} listSources={listSources} />;
};

export default Seasons;
