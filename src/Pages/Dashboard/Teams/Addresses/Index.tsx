import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';

const Index = () => {
  // begin component common config
  const pluralName = 'Addresses'
  const singularName = 'Address'
  const uri = '/dashboard/teams/addresses'
  const componentId = Str.slug(pluralName)
  const search = true
  const columns = [
    {
      key: 'name',
    },
    {
      key: 'Created_at',
    },
    {
      key: 'Status',
    },
    {
      key: 'action',
    },
  ]
  // end component common config

  return <AutoPage pluralName={pluralName} singularName={singularName} uri={uri} columns={columns} componentId={componentId} search={search} />;
};

export default Index;