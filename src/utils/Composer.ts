import { SeasonInterface, TeamInterface } from "@/interfaces/FootballInterface";
import Str from "./Str";

class GameComposer {

    static team(team: TeamInterface, prefers: 'name' | 'short' | 'TLA' | null = null) {
        var fallback_name = team.name || team.short_name || team.tla
        return prefers === 'short' ? team.short_name || fallback_name : (prefers === 'TLA' ? team.tla || fallback_name : team.name || fallback_name)
    }

    static season(season: SeasonInterface) {
        if (!season) return 'all'
        return `${Str.before(season.start_date, '-')} / ${Str.before(season.end_date, '-')}`
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

    static winnerId(game: any) {

        const { score } = game
        if (!score?.winner) return null

        if (score.winner === 'DRAW') return null

        if (score.winner === 'HOME_TEAM') {
            return game.home_team_id
        } else if (score.winner === 'AWAY_TEAM') {
            return game.away_team_id
        }

        return null
    }

    static hasResults(game: any) {

        const { score } = game
        if (!score?.winner) return null

        if (score.winner === 'DRAW') return true

        if (score.winner === 'HOME_TEAM') {
            return true
        } else if (score.winner === 'AWAY_TEAM') {
            return true
        }

        return null
    }

}

export default GameComposer