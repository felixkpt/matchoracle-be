import useAxios from '@/hooks/useAxios';
import React, { useEffect, useState } from 'react';
import { CompetitionInterface, SeasonInterface, TeamInterface } from '@/interfaces/CompetitionInterface';

import AutoTable from '@/components/AutoTable';
import PageHeader from '@/components/PageHeader';
import AutoModal from '@/components/AutoModal';

interface Props {
    record: CompetitionInterface | undefined;
}

const Teams: React.FC<Props> = ({ record }) => {
    const competition = record
    
    const [modelDetails, setModelDetails] = useState<any>(null);
    const [selectedSeason, setSelectedSeason] = useState<string | null>(null);
    const [seasons, setSeasons] = useState<SeasonInterface[] | null>(null);
    const [teams, setTeams] = useState<TeamInterface[] | null>(null);
    const [competitionTeamsUri, setCompetitionTeamsUri] = useState<string>();
    const { get, loading } = useAxios<TeamInterface[]>();

    useEffect(() => {
        if (competition) {
            get(`admin/competitions/view/${competition.id}/seasons`).then((res) => {
                if (res) {
                    setSeasons(res);
                    if (res.length > 0) {
                        // Set the first season as the default selected season
                        setSelectedSeason(res[0].id);
                    }
                }
            });
        }
    }, [competition]);

    useEffect(() => {
        if (competition && selectedSeason) {
            setCompetitionTeamsUri(`admin/competitions/view/${competition.id}/teams/${selectedSeason}`)
        }
    }, [competition, selectedSeason]);


    if (loading) {
        return <div>Loading...</div>;
    }

    const columns = [
        { key: 'Crest' },
        { label: 'Name', key: 'name' },
        { label: 'Short Name', key: 'short_name' },
        { label: 'TLA', key: 'tla' },
        { label: 'Country', key: 'country.name' },
        { label: 'Priority Number', key: 'priority_number' },
        { label: 'Last Updated', key: 'last_updated' },
        { label: 'Status', key: 'Status' },
        { label: 'User', key: 'user_id' },
        { label: 'Created At', key: 'Created_at' },
        { label: 'Action', key: 'action' },
    ];

    return (
        <div>
            <PageHeader title={'Teams List'} action="button" actionText="Create Team" actionTargetId="AutoModal" permission="admin/teams" />
            <div>
                {
                    competitionTeamsUri &&
                    <AutoTable columns={columns} baseUri={competitionTeamsUri} search={true} getModelDetails={setModelDetails} />
                }
            </div>
            {modelDetails && <AutoModal modelDetails={modelDetails} actionUrl="/admin/teams" />}
        </div>
    );
};

export default Teams;
