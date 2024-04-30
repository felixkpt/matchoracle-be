import { GetItem } from '@/interfaces'

type Props = {
    game: GetItem
}

import AutoTable from '@/components/Autos/AutoTable';

const Odds = ({ game }: Props) => {

    const baseUri = 'odds/' + game.data.year;

    const listUri = '?game_id=' + game.data.id;
    
    const search = true;
    const columns = [
      { label: 'game_id', key: 'id' },
      { label: 'date_time', key: 'date_time' },
      { label: 'home_team', key: 'home_team' },
      { label: '1X2 Odds', key: '1x2_odds' },
      { label: 'away_team', key: 'away_team' },
      { label: 'source', key: 'source' },
    ]  

    return (
        <div>
            <AutoTable
                baseUri={baseUri}
                listUri={listUri}
                singleUri={'/odds/odds/{year}/{id}'}
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

export default Odds;
