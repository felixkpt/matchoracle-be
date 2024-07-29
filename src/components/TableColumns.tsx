import { formatFTScores, formatHTScores, renderBTS, renderCS, renderFT1X2, renderFT1X2Pick, renderGameViewLink, renderHT1X2, renderHT1X2Pick, renderOver25, UTCDate } from "@/components/HtmlRenderers"
import { ColumnInterface } from "@/interfaces/UncategorizedInterfaces"

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
    { label: 'Status', key: 'Status' },
    { key: 'Game' },

]
