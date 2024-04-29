import LastXResultsIcons from "@/components/Teams/LastXResultsIcons";
import { TeamInterface } from "@/interfaces/FootballInterface";
import { teamLogo } from "@/utils/helpers";
import { NavLink } from "react-router-dom";

type TeamInfoProps = {
    team: TeamInterface;
    recentResults: string[];
};

const RenderTeamLogoAndForm = ({ team, recentResults }: TeamInfoProps) => {

    return (
        <>
            <NavLink to={`/admin/teams/view/${team.id}`}>
                <img className="symbol-image-lg" src={teamLogo(team.logo)} alt="" />
            </NavLink>
            <LastXResultsIcons results={recentResults} size="sm" />
        </>
    );
};

export default RenderTeamLogoAndForm