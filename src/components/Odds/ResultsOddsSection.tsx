import { GameInterface, OddInterface } from "@/interfaces/FootballInterface";
import { useEffect, useState } from "react";

type Props = {
    game: GameInterface;
};

const ResultsOddsSection = ({ game }: Props) => {
    const [odds, setOdds] = useState<OddInterface>()

    useEffect(() => {

        if (game.odds[0]) {
            setOdds(game.odds[0])
        }

    }, [game])

    return (
        <div>
            {odds ?
                <div className='odds-section'>
                    <div className='odds-btn'><span className='odd-title'>1</span><span>{odds.home_win_odds || '-'}</span></div>
                    <div className='odds-btn'><span className='odd-title'>X</span><span>{odds.draw_odds || '-'}</span></div>
                    <div className='odds-btn'><span className='odd-title'>2</span><span>{odds.away_win_odds || '-'}</span></div>
                </div>

                : ''}
        </div>
    );
};

export default ResultsOddsSection;
