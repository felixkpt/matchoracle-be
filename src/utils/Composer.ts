import { TeamInterface } from "@/interfaces/FootballInterface";

class Composer {

    static team(team: TeamInterface, prefers: 'name' | 'short' | 'TLA' | null = null) {
        return prefers === 'short' ? team.short_name : (prefers === 'TLA' ? team.tla : team.name)
    }

    static results(score: any, type: 'ft' | 'ht' | 'winner' = 'ft', show: 'h' | 'a' | null = null) {
        if (type === 'ft') {
            let h = score?.home_scores_full_time
            let a = score?.away_scores_full_time
            if (h && a) {
                return show === 'h' ? h : (show === 'a' ? a : `${h} - ${a}`)
            }
            else return '-'
        } else if (type === 'ht') {
            let h = score?.home_scores_half_time
            let a = score?.away_scores_half_time

            if (h && a) {
                return show === 'h' ? h : (show === 'a' ? a : `${h} - ${a}`)
            }
            else return '-'

        } else return score?.winner

    }

    static winner(game: any, teamId: string) {

        const { score } = game
        if (!score?.winner) return 'U'

        if (score.winner === 'DRAW') return 'D'

        if (score.winner === 'HOME_TEAM') {
            if (game.home_team_id == teamId) {
                return 'W'
            } else
                return 'L'
        } else if (score.winner === 'AWAY_TEAM') {
            if (game.away_team_id == teamId) {
                return 'W'
            } else
                return 'L'
        }
        return 'U'
    }

    static winningSide(game: any) {

        const { score } = game
        if (!score?.winner) return 'U'

        if (score.winner === 'DRAW') return 'D'

        if (score.winner === 'HOME_TEAM') {
            return 'h'
        } else if (score.winner === 'AWAY_TEAM') {
            return 'a'
        }

        return 'u'
    }

}

export default Composer