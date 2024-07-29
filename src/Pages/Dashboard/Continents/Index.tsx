import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';

const Index = () => {
    // begin component common config
    const pluralName = 'Continents'
    const singularName = 'Continent'
    const uri = '/dashboard/continents'
    const componentId = Str.slug(pluralName)
    const search = true
    const columns = [
        {
            label: 'Flag',
            key: 'flag',
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
    ]
    // end component common config

    return <AutoPage pluralName={pluralName} singularName={singularName} uri={uri} columns={columns} componentId={componentId} search={search} />;
};

export default Index;
