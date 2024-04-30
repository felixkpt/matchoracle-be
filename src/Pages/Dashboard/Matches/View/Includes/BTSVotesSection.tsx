import BTSOddsSection from '@/components/Odds/BTSOddsSection'
import useAxios from '@/hooks/useAxios'
import { GameInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'

type Props = {
    game: GameInterface
}

const BTSVotesSection = ({ game: initialGame }: Props) => {

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
            post(`dashboard/matches/view/${game.id}/vote`, { type: 'bts', vote }).then((res) => {
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
                const totals = game.gg_votes + game.ng_votes

                let gg_votes = (game.gg_votes / totals) * 100 || 50
                let ng_votes = (game.ng_votes / totals) * 100 || 50

                setbutton1Width(gg_votes + '%')
                setbutton2Width(ng_votes + '%')

            }, 100);

            setVoted(!!game.current_user_votes?.bts)
        }

    }, [game])

    useEffect(() => {

        if (!isFuture || voted) {
            setTimeout(() => {
                setShowVotes(true);
            }, 200);
        }

    }, [isFuture, voted, game])

    useEffect(() => {

        if (isFuture || voted) {
            const transitionedElement = document.querySelector('.bts-transistion');
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
            <h6>BTS odds & votes</h6>
            <BTSOddsSection game={game} />
            {
                game &&

                <>
                    {(!isFuture || voted) ? (
                        <div className='col-12 d-flex align-items-end overflow-hidden'>
                            <div className='transistion bts-transistion d-flex flex-column' style={{ width: button1Width }}>
                                <span className={`vote-counts ${showText ? 'shown' : ''}`}>{game.gg_votes} votes{game.current_user_votes ? (game.current_user_votes.bts === 'gg' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='BTS - Yes votes' className={`vote-btn home fw-bold text-start ${showVotes ? 'shown' : ''}`}>GG</div>
                            </div>
                            <div className='transistion bts-transistion d-flex flex-column' style={{ width: button2Width }}>
                                <span className={`vote-counts text-center ${showText ? 'shown' : ''}`}>{game.ng_votes} votes{game.current_user_votes ? (game.current_user_votes.bts === 'ng' ? <span className="text-primary ms-1">(You)</span> : '') : ''}</span>
                                <div title='BTS - No votes' className={`vote-btn draw fw-bold text-start ${showVotes ? 'shown' : ''}`}>NG</div>
                            </div>
                        </div>
                    ) : (
                        <div>
                            <div className='col-12'>GG/NG?</div>
                            <div className='col-12'>
                                <div className='col-12'>
                                    <div className='d-flex justify-content-center'>
                                        <div onClick={handleVote} data-target='gg' title='Predict BTS - Yes' className='col vote-btn home fw-bold text-center p-1'>GG</div>
                                        <div onClick={handleVote} data-target='ng' title='Predict BTS - No' className='col vote-btn draw fw-bold text-center p-1'>NG</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </>
            }

        </div>
    );

}

export default BTSVotesSection