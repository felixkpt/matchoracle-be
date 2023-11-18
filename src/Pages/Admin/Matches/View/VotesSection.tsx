import useAxios from '@/hooks/useAxios'
import { GameInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'

type Props = {
    game: GameInterface
}

const VotesSection = ({ game }: Props) => {

    const [localGame, setLocalGame] = useState<GameInterface>()

    const { post } = useAxios()

    const [isFuture, setIsFuture] = useState(true)

    const [voted, setVoted] = useState(false)
    const [showVotes, setShowVotes] = useState(false)

    const [homeButtonWidth, setHomeButtonWidth] = useState<string>('0');
    const [drawButtonWidth, setDrawButtonWidth] = useState<string>('0');
    const [awayButtonWidth, setAwayButtonWidth] = useState<string>('0');
    const [showText, setShowText] = useState(false);
    const [votingInProgress, setVotingInProgress] = useState(false); // State to track voting in progress

    useEffect(() => {
        if (game) {
            setLocalGame(game)
        }
    }, [game])

    function handleVote(e: any) {

        if (localGame && !voted && !votingInProgress) {
            setVotingInProgress(true);

            const vote = e.target.getAttribute('data-target')
            post(`admin/matches/view/${localGame.id}/vote`, { type: 'winner', vote }).then((res) => {
                if (res) {
                    setLocalGame(res.data)
                }

            }).finally(() => setVotingInProgress(false))

        }

    }

    useEffect(() => {

        if (localGame) {
            setIsFuture(localGame.is_future)
            setTimeout(() => {
                const totals = localGame.home_win_votes + localGame.draw_votes + localGame.away_win_votes

                let home = (localGame.home_win_votes / totals) * 100 || 33
                let draw = (localGame.draw_votes / totals) * 100 || 33
                let away = (localGame.away_win_votes / totals) * 100 || 33

                setHomeButtonWidth(home + '%')
                setDrawButtonWidth(draw + '%')
                setAwayButtonWidth(away + '%')

            }, 100);

            setVoted(localGame.current_user_votes)
        }

    }, [localGame])

useEffect(() => {

        if (!isFuture || voted) {
            setTimeout(() => {
                setShowVotes(true);
            }, 200);
        }

    }, [isFuture, voted, localGame])

    useEffect(() => {

        if (isFuture || voted) {
            const transitionedElement = document.querySelector('.transistion');
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
        <div className='vote-section shadow-sm p-2 rounded mb-4 row justify-content-between border noselect'>
            <h6>Final results votes</h6>
            {
                localGame &&

                <>
                    {(!isFuture || voted) ? (
                        <div className='col-12 d-flex align-items-end overflow-hidden'>
                            <div className='transistion d-flex flex-column' style={{ width: homeButtonWidth }}>
                                <span className={`vote-counts ${showText ? 'shown' : ''}`}>{localGame.home_win_votes} votes</span>
                                <div title='Home win votes' className={`vote-btn home fw-bold text-start ${showVotes ? 'shown' : ''}`}>1</div>
                            </div>
                            <div className='transistion d-flex flex-column' style={{ width: drawButtonWidth }}>
                                <span className={`vote-counts text-center ${showText ? 'shown' : ''}`}>{localGame.draw_votes} votes</span>
                                <div title='Draw votes' className={`vote-btn draw fw-bold text-start ${showVotes ? 'shown' : ''}`}>X</div>
                            </div>
                            <div className='transistion d-flex flex-column' style={{ width: awayButtonWidth }}>
                                <span className={`vote-counts text-end ${showText ? 'shown' : ''}`}>{localGame.away_win_votes} votes</span>
                                <div title='Away win votes' className={`vote-btn away fw-bold text-start bg-secondary ${showVotes ? 'shown' : ''}`}>2</div>
                            </div>
                        </div>
                    ) : (
                        <div>
                            <div className='col-12'>Who will win?</div>
                            <div className='col-12 row justify-content-between'>
                                <div onClick={handleVote} data-target='home' title='Predict Home win' className='col vote-btn home fw-bold text-center p-1'>1</div>
                                <div onClick={handleVote} data-target='draw' title='Predict Draw' className='col vote-btn draw fw-bold text-center p-1'>X</div>
                                <div onClick={handleVote} data-target='away' title='Predict Away win' className='col vote-btn away fw-bold text-center bg-secondary p-1'>2</div>
                            </div>
                        </div>
                    )}
                </>
            }

        </div>
    );

}

export default VotesSection