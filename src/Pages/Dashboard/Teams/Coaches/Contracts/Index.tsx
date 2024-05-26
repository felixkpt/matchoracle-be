import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';
import useListSources from '@/hooks/list-sources/useListSources';

const Index = () => {
  // begin component common config
  const pluralName = 'Contracts'
  const singularName = 'Contract'
  const uri = '/dashboard/teams/coaches/contracts'
  const componentId = Str.slug(pluralName)
  const search = true
  const columns = [
    {
      key: 'coach.name',
    },
    {
      key: 'team.name',
    },
    {
      key: 'start',
    },
    {
      key: 'until',
    },
    {
      key: 'Status',
    },
    {
      key: 'action',
    },
  ]
  // end component common config

  const { competitions: listSources } = useListSources()

  return <AutoPage pluralName={pluralName} singularName={singularName} uri={uri} columns={columns} componentId={componentId} search={search} listSources={listSources} />;
};

export default Index;