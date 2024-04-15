export const predictionsColumns = [
    { key: 'Game' },
    { key: 'HT_HDA', label: 'HT 1X2' },
    { key: 'HT_HDA_PICK', label: 'HT Pick' },
    { key: 'FT_HDA', label: 'FT 1X2' },
    { key: 'FT_HDA_PICK', label: 'FT Pick' },
    { key: 'BTS' },
    { key: 'Over25' },
    { key: 'CS' },
    { label: 'half_time', key: 'Halftime' },
    { label: 'full_time', key: 'Fulltime' },
    { label: 'Status', key: 'Status' },
    { label: 'UTC_date', key: 'UTC_date' },
    { label: 'Predicted', key: 'Predicted' },
    { label: 'Action', key: 'action' },
]

export const oddsColumns = [
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

export const predictionModes = [
    {
        id: 1,
        name: 'Default',
    },
    {
        id: 2,
        name: 'Source',
    },
]