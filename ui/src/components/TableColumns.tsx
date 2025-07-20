import { formatFTScores, formatHTScores, renderBTS, renderCS, renderFT1X2, renderFT1X2Pick, renderGameViewLink, renderHT1X2, renderHT1X2Pick, renderOver25, UTCDate, renderDateTime } from "@/components/HtmlRenderers"
import { ColumnInterface } from "@/interfaces/UncategorizedInterfaces"
import Str from "@/utils/Str"
import { NavLink } from "react-router-dom"

export const predictionsColumns: ColumnInterface[] = [
    {
        key: 'Game',
        renderCell: renderGameViewLink,
    },
    {
        key: 'HT_HDA', label: 'HT 1X2',
        renderCell: renderHT1X2,
    },
    {
        key: 'HT_HDA_PICK', label: 'HT Pick',
        renderCell: renderHT1X2Pick,
    },
    {
        key: 'FT_HDA', label: 'FT 1X2',
        renderCell: renderFT1X2,
    },
    {
        key: 'FT_HDA_PICK', label: 'FT Pick',
        renderCell: renderFT1X2Pick,
    },
    {
        key: 'BTS',
        renderCell: renderBTS,

    },
    {
        key: 'Over25',
        renderCell: renderOver25,

    },
    {
        key: 'CS',
        renderCell: renderCS,
    },
    {
        label: 'half_time', key: 'Halftime',
        renderCell: formatHTScores,

    },
    {
        label: 'full_time', key: 'Fulltime',
        renderCell: formatFTScores,

    },
    { label: 'Status', key: 'Status' },
    {
        label: 'UTC_date', key: 'UTC_date',
        renderCell: UTCDate,

    },
    { label: 'Predicted', key: 'Predicted' },
    { label: 'Action', key: 'action' },
]

export const oddsColumns: ColumnInterface[] = [
    { key: 'Date' },
    { key: 'home_team' },
    { key: 'away_team' },
    { key: 'home_win' },
    { key: 'draw' },
    { key: 'away_win' },
    { key: 'over_25' },
    { key: 'under_25' },
    { key: 'GG' },
    { key: 'NG' },
    {
        label: 'Updated', key: 'updated_at', renderCell: (key, _) => renderDateTime(key, true),
    },
    { label: 'Status', key: 'Status' },
    {
        key: 'Game', renderCell: (_: string, record: any) => {
            return record.game_id ? <NavLink to={`/dashboard/matches/view/${record.game_id}`} target="_blank">#{record.game_id}</NavLink> : 'N/A'
        }
    },

]

export const jobLogsColumns = [
    { key: 'date' },
    { key: 'job_run_counts', label: 'Job Runs' },
    { key: 'competition_counts', label: 'Compe Counts' },
    { key: 'run_competition_counts', label: 'Compe Runs' },
    { key: 'action_counts', label: 'Action Counts' },
    { key: 'run_action_counts', label: 'Action Runs' },
    { key: 'average_seconds_per_action', label: 'Avg Secs/Action' },
    { key: 'created_counts', label: 'Created' },
    { key: 'updated_counts', label: 'Updated' },
    { key: 'failed_counts', label: 'Failed' },
    {
        key: 'Remaining_time', label: 'Remaining Time',
        renderCell: (_: string, record: any) => {
            return record.Remaining_time >= 0 ? Str.formatTime(record.Remaining_time) : 'N/A'
        }
    },

    { label: 'Last run', key: 'Last_run' },
    { label: 'Created', key: 'Created_at' },
]

