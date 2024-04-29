import RenderTeamLogoAndForm from "@/Pages/Admin/Matches/View/RenderTeamLogoAndForm"
import TeamMatchesCard from "@/components/Teams/TeamMatchesCard"
import useAxios from "@/hooks/useAxios"
import { GameInterface, TeamInterface } from "@/interfaces/FootballInterface"
import { teamLogo } from "@/utils/helpers"
import { useEffect, useState } from "react"
import { NavLink } from "react-router-dom"

type Props = {
  record: TeamInterface | undefined
}

const Matches = ({ record }: Props) => {

  const team = record

  const { get: getTeamGames } = useAxios()

  const [teamGames, setTeamGames] = useState<GameInterface[]>()


  // Getting team games
  useEffect(() => {

    if (team) {
      getTeamGames(`admin/teams/view/${team.id}/matches?type=past&with_upcoming=1&before_to_date=1&per_page=15`).then((res) => {

        const { data: data1 } = res
        if (data1) {
          setTeamGames(data1)
        }
      })
    }
  }, [team])

  return (
    <div>
      {
        team &&
          <TeamMatchesCard team={team} teamGames={teamGames} />
      }

    </div>
  )
}

export default Matches