import Str from '@/utils/Str';
import AutoPage from '@/components/Autos/AutoPage';

const Index = () => {
  // begin component common config
  const pluralName = 'GameScore Statuses'
  const singularName = 'GameScore Status'
  const uri = '/dashboard/settings/picklists/statuses/game-score-statuses'
  const componentId = Str.slug(pluralName)
  const search = true
  const columns = [
    {
      label: 'ID',
      key: 'id',
    },
    {
      label: 'Status Name',
      key: 'name',
    },
    {
      label: 'Slug',
      key: 'slug',
    },
    {
      label: 'Icon',
      key: 'Icon',
    },
    {
      label: 'Class',
      key: 'class',
    },
    { key: 'Created_by' },
    {
      label: 'Created At',
      key: 'created_at',
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