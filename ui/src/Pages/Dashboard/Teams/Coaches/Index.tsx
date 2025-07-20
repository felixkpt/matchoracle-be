import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';
import useListSources from '@/hooks/list-sources/useListSources';

const Index = () => {
  // begin component common config
  const pluralName = 'Coaches'
  const singularName = 'Coach'
  const uri = '/dashboard/teams/coaches'
  const componentId = Str.slug(pluralName)
  const search = true
  const columns = [
    {
      key: 'name',
    },
    {
      key: 'created_at',
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

  return <AutoPage pluralName={pluralName} singularName={singularName} uri={uri} columns={columns} componentId={componentId} search={search} listSources={listSources} modalSize='modal-lg' />;
};

export default Index;