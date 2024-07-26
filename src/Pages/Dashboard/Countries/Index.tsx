import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';
import useListSources from '@/hooks/list-sources/useListSources';

const Index = () => {
    // begin component common config
    const pluralName = 'Countries'
    const singularName = 'Country'
    const uri = '/dashboard/countries'
    const componentId = Str.slug(pluralName)
    const search = true
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
        key: 'created_at',
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
    const { competitions: listSources } = useListSources()

    return <AutoPage pluralName={pluralName} singularName={singularName} uri={uri} columns={columns} componentId={componentId} search={search} listSources={listSources} />;
};

export default Index;
