type Props = {
    winner: string | undefined
}

const ResultsIcon = ({ winner }: Props) => {
    return (
        <div>
            {winner === 'W' ? <span className='rounded-circle border p-1 bg-success text-white d-inline-block text-center lh-sm' style={{ width: 30, height: 30 }}>W</span> : (winner === 'D' ? <span className='rounded-circle border p-1 bg-warning text-white d-inline-block text-center lh-sm' style={{ width: 30, height: 30 }}>D</span> : winner === 'L' ? <span className='rounded-circle border p-1 bg-danger text-white d-inline-block text-center lh-sm text-center' style={{ width: 30, height: 30 }}>L</span> : <span className='rounded-circle border p-1 bg-secondary text-white d-inline-block text-center lh-sm text-center' style={{ width: 30, height: 30 }}>{winner}</span>)}
        </div>
    )
}

export default ResultsIcon