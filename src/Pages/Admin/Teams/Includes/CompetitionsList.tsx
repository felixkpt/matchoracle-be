import Loader from "@/components/Loader";
import { useAuth } from "@/contexts/AuthContext";
import useAxios from "@/hooks/useAxios";
import { CompetitionInterface, CountryInterface, TeamInterface } from "@/interfaces/CompetitionInterface";
import { baseURL } from "@/utils/helpers";
import { Icon } from "@iconify/react/dist/iconify.js";
import { useState } from "react";
import { NavLink } from "react-router-dom";

interface Props {
    country: CountryInterface;
    competitions: CompetitionInterface[];
}

const CompetitionsList: React.FC<Props> = ({ country, competitions }) => {
    const { fileAccessToken } = useAuth()

    const [teams, setTeams] = useState<{ [key: string]: TeamInterface[] }>();
    const [loadingTeams, setLoadingTeams] = useState<{ [key: string]: boolean }>({});
    const { get } = useAxios();

    function prepareLoadCompetitionTeams(e: any) {
        e.preventDefault();
        const target = e?.target.closest('.accordion-button');

        loadCompetitionTeams(target)
    }

    function loadCompetitionTeams(eventOrTarget: any) {
        let target;
        let isEvent = false;

        if (eventOrTarget.nativeEvent instanceof Event) {
            isEvent = true;
            eventOrTarget.preventDefault();
            target = eventOrTarget.target;
        } else {
            target = eventOrTarget;
        }

        if (target) {
            const isExpanded = target.getAttribute('aria-expanded');
            const competitionId = target.getAttribute('data-competition-id');

            if (isExpanded && competitionId && (!teams || (teams && !teams[competitionId]))) {
                // Set loading state for the current competition
                handleLoadingTeams(competitionId, true);

                get(`admin/teams/competition/${competitionId}`).then((res: any) => {
                    if (res?.data) {
                        setTeams((curr) => ({ ...curr, [competitionId]: res.data }));
                    }
                    // Clear loading state for the current competition
                    handleLoadingTeams(competitionId, false);
                });
            }
        }
    }

    function handleLoadingTeams(competitionId: string, state: boolean) {
        setLoadingTeams((curr) => ({ ...curr, [competitionId]: state }));
    }

    return (
        <div>
            <div className="accordion" id={`${country.id}TeamsAccordion`}>
                {competitions.map((competition: CompetitionInterface) => {

                    const teamsLoading = loadingTeams[competition.id];
                    const teamsExist = teams && teams[competition.id] && teams[competition.id].length > 0;

                    const loaderContent =
                        teamsLoading ? (
                            <div className="p-2 rounded bg-light">
                                <Loader message="Loading teams..." justify="start" />
                            </div>
                        ) : teamsExist ? null : 'No teams';

                    return (
                        <div key={competition.id} className="accordion-item">
                            <h2 className="accordion-header" id={`heading${competition.id}`}>
                                <button className="accordion-button collapsed" type="button" onClick={loadCompetitionTeams} data-competition-id={competition.id} data-bs-toggle="collapse" data-bs-target={`#collapse${competition.id}`} aria-expanded="false" aria-controls={`collapse${competition.id}`}>
                                    <NavLink to={`/admin/competitions/view/${competition.id}`} onClick={prepareLoadCompetitionTeams} className="text-decoration-none text-dark">
                                        <img src={`${competition.emblem || baseURL(`admin/file-repo/public/football/defaultflag.png?token=${fileAccessToken}`)}`} className="rounded-circle me-2 bg-body-secondary border" style={{ width: "24px", height: "24px" }} alt="" /><span>{competition.name}</span>
                                    </NavLink>
                                </button>
                            </h2>
                            <div id={`collapse${competition.id}`} className="accordion-collapse collapse" aria-labelledby={`heading${competition.id}`}>
                                <div className="accordion-body striped-section position-relative">
                                    {loaderContent}
                                    {teams && teams[competition.id] && teams[competition.id].map((team) => (
                                        <div className="ms-2 mt-1 rounded" key={team.id}>
                                            <div className="border-start border-dark-subtle border-2 rounded p-2">
                                                <NavLink to={`/admin/teams/view/${team.id}`} className="text-decoration-none text-dark d-flex justify-content-between align-items-center w-100">
                                                    <span><img src={`${team.crest}`} className="rounded-circle me-2 bg-body-secondary border" style={{ width: "19px", height: "19px" }} alt="" /> <span>{team.name}</span></span><Icon icon='ph:caret-right-bold' />
                                                </NavLink>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )
                })}
            </div>
        </div>
    );
};

export default CompetitionsList;
