type Props = {
    winner: string | undefined
    size?: 'sm' | 'md'
}

const ResultsIcon = ({ winner, size }: Props) => {

    const styles: any = []
    return (
        <div className="no-select">
            {winner === 'W' ? <span className={`rounded-circle border p-1 bg-success text-white d-inline-block text-center results-icon-${size || 'md'}`} style={styles}>W</span> : (winner === 'D' ? <span className={`rounded-circle border p-1 bg-warning text-white d-inline-block text-center results-icon-${size || 'md'}`}>D</span> : winner === 'L' ? <span className={`rounded-circle border p-1 bg-danger text-white d-inline-block text-center results-icon-${size || 'md'}`}>L</span> : <span className={`rounded-circle border p-1 bg-secondary text-white d-inline-block text-center results-icon-${size || 'md'}`}>{winner}</span>)}
        </div>
    )
}

export default ResultsIcon