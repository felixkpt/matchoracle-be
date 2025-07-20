
type Props = {
    title?: string
    competition: any
    setCompetition: any
}

const Nav = ({ title, competition, setCompetition }: Props) => {
    console.log(competition, setCompetition)
    return (
        <div>
            <h4>{title}</h4>
        </div>
    )
}

export default Nav