import { GameInterface, OddInterface } from "@/interfaces/FootballInterface";
import { useEffect, useState } from "react";

type Props = {
    game: GameInterface;
};

const OverUnderOddsSection = ({ game }: Props) => {
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
                    <div className='odds-btn'><span className='odd-title'>OV</span><span>{odds.over_25_odds || '-'}</span></div>
                    <div className='odds-btn'><span className='odd-title'>UN</span><span>{odds.under_25_odds || '-'}</span></div>
                </div>

                : ''}
        </div>
    );
};

export default OverUnderOddsSection;
