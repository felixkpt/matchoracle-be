import OverUnderOddsSection from '@/components/Odds/OverUnderOddsSection'
import useAxios from '@/hooks/useAxios'
import { GameInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'

type Props = {
    game: GameInterface
}

const OverUnderVotesSection = ({ game: initialGame }: Props) => {

    const { post } = useAxios()

    const [game, setGame] = useState<GameInterface>(initialGame)

    const [isFuture, setIsFuture] = useState(true)
    const [voted, setVoted] = useState(false)
    const [showVotes, setShowVotes] = useState(false)

    const [button1Width, setbutton1Width] = useState<string>('0');
    const [button2Width, setbutton2Width] = useState<string>('0');
    const [showText, setShowText] = useState(false);
    const [votingInProgress, setVotingInProgress] = useState(false); // State to track voting in progress

    function handleVote(e: any) {

        if (game && !voted && !votingInProgress) {
            setVotingInProgress(true);

            const vote = e.target.getAttribute('data-target')
            post(`admin/matches/view/${game.id}/vote`, { type: 'over_under', vote }).then((res) => {
                if (res) {
                    setGame(res.data)
                }

            }).finally(() => setVotingInProgress(false))

        }

    }

    useEffect(() => {

        if (game) {
            setIsFuture(game.is_future)
            setTimeout(() => {
                const totals = game.over_votes + game.under_votes

                let over_votes = (game.over_votes / totals) * 100 || 50
                let under_votes = (game.under_votes / totals) * 100 || 50

                setbutton1Width(over_votes + '%')
                setbutton2Width(under_votes + '%')

            }, 100);

            setVoted(!!game.current_user_votes?.over_under)
        }

    }, [game, game.current_user_votes])

    useEffect(() => {

        if (!isFuture || voted) {
            setTimeout(() => {
                setShowVotes(true);
            }, 200);
        }

    }, [isFuture, voted, game])

    useEffect(() => {

        if (isFuture || voted) {
            const transitionedElement = document.querySelector('.over-under-transistion');
            transitionedElement && transitionedElement.addEventListener('transitionend', handleTransitionEnd);

            return () => {
                transitionedElement && transitionedElement.removeEventListener('transitionend', handleTransitionEnd);
            };

        }

    }, [isFuture, voted])

    const handleTransitionEnd = () => {
        setTimeout(() => {
            setShowText(true);
        }, 1000);
    };

    return (
        <div className='vote-section shadow-sm p-2 rounded mb-4 row justify-content-between border no-select'>
            <h6>Over/Under 25 odds & votes</h6>
            <OverUnderOddsSection game={game} />

            {
                game &&

                <div>
                    {(!isFuture || voted) ? (
                        <div className='col-12 d-flex align-items-end overflow-hidden'>
                            <div className='transistion over-under-transistion d-flex flex-column' style={{ width: button1Width }}>
                                <span className={`vote-counts ${showText ? 'shown' : ''}`}>{game.over_votes} votes{game.current_user_votes ? (game.current_user_votes.over_under === 'over' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='Over 2.5 votes' className={`vote-btn home fw-bold text-start ${showVotes ? 'shown' : ''}`}>OV</div>
                            </div>
                            <div className='transistion over-under-transistion d-flex flex-column' style={{ width: button2Width }}>
                                <span className={`vote-counts text-center ${showText ? 'shown' : ''}`}>{game.under_votes} votes{game.current_user_votes ? (game.current_user_votes.over_under === 'under' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='Under 2.5 votes' className={`vote-btn draw fw-bold text-start ${showVotes ? 'shown' : ''}`}>UN</div>
                            </div>
                        </div>
                    ) : (
                        <div>
                            <div className='col-12'>Over/Under 2.5?</div>
                            <div className='col-12'>
                                <div className='col-12'>
                                    <div className='d-flex justify-content-center'>
                                        <div onClick={handleVote} data-target='over' title='Predict Over 2.5' className='col vote-btn home fw-bold text-center p-1'>OV</div>
                                        <div onClick={handleVote} data-target='under' title='Predict Under 2.5' className='col vote-btn draw fw-bold text-center p-1'>UN</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            }

        </div>
    );

}

export default OverUnderVotesSection