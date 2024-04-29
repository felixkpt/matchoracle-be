import { GameInterface, OddInterface } from "@/interfaces/FootballInterface";
import { useEffect, useState } from "react";

type Props = {
    game: GameInterface;
};

const BTSOddsSection = ({ game }: Props) => {
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
                    <div className='odds-btn'><span className='odd-title'>GG</span><span>{odds.gg_odds || '-'}</span></div>
                    <div className='odds-btn'><span className='odd-title'>NG</span><span>{odds.ng_odds || '-'}</span></div>
                </div>

                : ''}
        </div>
    );
};

export default BTSOddsSection;
