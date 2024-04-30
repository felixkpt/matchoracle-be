import Loader from '@/components/Loader'
import useAxios from '@/hooks/useAxios'
import { GameInterface, StandingInterface, TeamInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import LastMatches from './LastMatches'
import StandingsTable from '@/components/Teams/StandingsTable'
import MatchPageHeader from './MatchPageHeader'
import ResultsVotesSection from './Includes/ResultsVotesSection'
import Head2HeadCard from '@/components/Teams/Head2HeadCard'
import PredictionsSection from './PredictionsSection'
import InlineAction from '@/components/Modals/InlineAction'
import { subscribe, unsubscribe } from '@/utils/events'
import usePermissions from '@/hooks/usePermissions'
import Error404 from '@/Pages/ErrorPages/Error404'

const Index = () => {

  const { get: getGame, loading } = useAxios()
  const { id } = useParams()

  const { userCan } = usePermissions()
  const [key, setKey] = useState<number>(0)
  const [game, setGame] = useState<GameInterface>()
  const [homeTeam, setHomeTeam] = useState<TeamInterface>()
  const [awayTeam, setAwayTeam] = useState<TeamInterface>()
  const defaultOutcomes = ['U', 'U', 'U', 'U', 'U']
  const [homeTeamRecentResults, setHomeTeamRecentResults] = useState<string[]>(defaultOutcomes)
  const [awayTeamRecentResults, setAwayTeamRecentResults] = useState<string[]>(defaultOutcomes)

  const { get: getStandings } = useAxios()
  const [standings, setStandings] = useState<StandingInterface[]>()

  // Getting game details
  useEffect(() => {

    if (id) {
      getGameDetails()
    }
  }, [id])

  async function getGameDetails() {
    getGame(`dashboard/matches/view/${id}?break_preds=1`).then((res) => {
      const { data } = res
      if (data) {
        handleSetGame(data)
      }
    })
  }

  function handleSetGame(game: GameInterface) {
    setGame(game);
    setHomeTeam(game.home_team);
    setAwayTeam(game.away_team);
    setKey((c) => c + 1);

  }

  // Getting standings
  useEffect(() => {

    if (game) {
      getStandings(`dashboard/competitions/view/${game.competition_id}/standings/${game.season_id}`).then((res) => {

        const { standings } = res

        if (standings) {
          setStandings(standings)
        }
      })
    }
  }, [game])

  useEffect(() => {

    subscribe('ajaxPostDone', handleAjaxPostDone);

    return () => {
      unsubscribe('ajaxPostDone', handleAjaxPostDone);
    };

  }, [id])

  const handleAjaxPostDone = (resp: any) => {
    if (resp.detail) {
      const detail = resp.detail;
      if (detail.elementId === 'update-game' && detail.results) {
        setKey((c) => c + 1)
        getGameDetails()
      }
    }
  };

  return (
    <div>

      {
        !loading ?

          game && homeTeam && awayTeam ?
            <div className='' key={key}>
              <div className="row">
                <div className="col-12 col-xl-9">
                  {userCan('dashboard/matches/view/{id}/update-game', 'post') &&
                    <div className="d-flex justify-content-end mb-3">
                      <InlineAction id='update-game' actionUrl={`dashboard/matches/view/${id}/update-game`}><button type="submit" className="btn btn-primary">Update game</button></InlineAction>
                    </div>
                  }
                  <MatchPageHeader game={game} homeTeam={homeTeam} awayTeam={awayTeam} homeTeamRecentResults={homeTeamRecentResults} awayTeamRecentResults={awayTeamRecentResults} />
                  <ResultsVotesSection game={game} />
                  <div className="row">
                    <div className="col-12">
                      <LastMatches game={game} homeTeam={homeTeam} awayTeam={awayTeam} perPage={30} withUpcoming={true} />
                    </div>
                    <div className="col-12 col-xl-8">
                      <LastMatches game={game} homeTeam={homeTeam} awayTeam={awayTeam} currentground={true} perPage={5} setHomeTeamRecentResults={setHomeTeamRecentResults} setAwayTeamRecentResults={setAwayTeamRecentResults} />
                    </div>
                  </div>
                </div>
                <div className="col-12 col-xl-3">
                  <div className='mb-5'>
                    <StandingsTable standings={standings} minimal={true} homeTeamId={homeTeam.id} awayTeamId={awayTeam.id} />
                  </div>
                  <div className='mb-5'>
                    <Head2HeadCard key={game.id} game={game} homeTeam={homeTeam} perPage={5} awayTeam={awayTeam} />
                  </div>
                </div>
                <div className="col-12">
                  <PredictionsSection game={game} />
                </div>
              </div>
            </div>
            :
            <Error404 timeout={1} />
          :
          <Loader />
      }

    </div>
  )
}

export default Index