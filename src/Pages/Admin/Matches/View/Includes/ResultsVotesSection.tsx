import ResultsOddsSection from '@/components/Odds/ResultsOddsSection'
import useAxios from '@/hooks/useAxios'
import { GameInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'

type Props = {
    game: GameInterface;
};

const ResultsVotesSection = ({ game: initialGame }: Props) => {

    const { post } = useAxios()

    const [game, setGame] = useState<GameInterface>(initialGame)

    const [isFuture, setIsFuture] = useState(true)
    const [voted, setVoted] = useState(false)
    const [showVotes, setShowVotes] = useState(false)

    const [button1Width, setbutton1Width] = useState<string>('0');
    const [button2Width, setbutton2Width] = useState<string>('0');
    const [button3Width, setbutton3Width] = useState<string>('0');
    const [showText, setShowText] = useState(false);
    const [votingInProgress, setVotingInProgress] = useState(false); // State to track voting in progress

    function handleVote(e: any) {

        if (game && !voted && !votingInProgress) {
            setVotingInProgress(true);

            const vote = e.target.getAttribute('data-target')
            post(`admin/matches/view/${game.id}/vote`, { type: 'winner', vote }).then((res) => {
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
                const totals = game.home_win_votes + game.draw_votes + game.away_win_votes

                let home = (game.home_win_votes / totals) * 100 || 33
                let draw = (game.draw_votes / totals) * 100 || 33
                let away = (game.away_win_votes / totals) * 100 || 33

                setbutton1Width(home + '%')
                setbutton2Width(draw + '%')
                setbutton3Width(away + '%')

            }, 100);

            setVoted(!!game.current_user_votes?.winner)
        }

    }, [game])

    useEffect(() => {

        if (!isFuture || voted) {
            setTimeout(() => {
                setShowVotes(true);
            }, 200);
        }

    }, [isFuture, voted, game])

    console.log(game.current_user_votes?.winner)


    useEffect(() => {

        if (isFuture || voted) {
            const transitionedElement = document.querySelector('.winner-transistion');
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
        <div className='vote-section shadow-sm p-2 rounded mb-5 row justify-content-between border noselect'>
            <div className="col-12">
                Fulltime odds & votes
                <ResultsOddsSection game={game} />
            </div>
            {
                game &&

                <>
                    {(!isFuture || voted) ? (
                        <div className='col-12 d-flex align-items-end overflow-hidden'>
                            <div className='transistion winner-transistion d-flex flex-column' style={{ width: button1Width }}>
                                <span className={`vote-counts ${showText ? 'shown' : ''}`}>{game.home_win_votes} votes{game.current_user_votes ? (game.current_user_votes.winner === 'home' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='Home win votes' className={`vote-btn home fw-bold text-start ${showVotes ? 'shown' : ''}`}>1</div>
                            </div>
                            <div className='transistion winner-transistion d-flex flex-column' style={{ width: button2Width }}>
                                <span className={`vote-counts text-center ${showText ? 'shown' : ''}`}>{game.draw_votes} votes{game.current_user_votes ? (game.current_user_votes.winner === 'draw' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='Draw votes' className={`vote-btn draw fw-bold text-start ${showVotes ? 'shown' : ''}`}>X</div>
                            </div>
                            <div className='transistion winner-transistion d-flex flex-column' style={{ width: button3Width }}>
                                <span className={`vote-counts text-end ${showText ? 'shown' : ''}`}>{game.away_win_votes} votes{game.current_user_votes ? (game.current_user_votes.winner === 'away' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='Away win votes' className={`vote-btn away fw-bold text-start bg-secondary ${showVotes ? 'shown' : ''}`}>2</div>
                            </div>
                        </div>
                    ) : (
                        <div>
                            <div className='col-12'>Who will win?</div>
                            <div className='col-12'>
                                <div className='d-flex justify-content-center'>
                                    <div onClick={handleVote} data-target='home' title='Predict Home win' className='col vote-btn home fw-bold text-center p-1'>1</div>
                                    <div onClick={handleVote} data-target='draw' title='Predict Draw' className='col vote-btn draw fw-bold text-center p-1'>X</div>
                                    <div onClick={handleVote} data-target='away' title='Predict Away win' className='col vote-btn away fw-bold text-center bg-secondary p-1'>2</div>
                                </div>
                            </div>
                        </div>
                    )}
                </>
            }

        </div>
    );

}

export default ResultsVotesSection