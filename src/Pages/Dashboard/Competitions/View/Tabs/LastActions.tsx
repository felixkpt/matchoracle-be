import NoContentMessage from '@/components/NoContentMessage';
import useAxios from '@/hooks/useAxios';
import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface';
import { Button, Table, Spinner, Row, Col, Card, CardBody } from 'react-bootstrap';
import { useState } from 'react';
import TimeAgo from 'timeago-react';

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const LastActions: React.FC<Props> = ({ record, getRecord }) => {
    const { post } = useAxios();
    const [loadingActions, setLoadingActions] = useState<{ [key: string]: boolean }>({});
    const [loading, setLoading] = useState(false); // Global loading state for any action

    const competition = record;

    if (!competition || !competition.last_action) return <NoContentMessage message="No last actions available" />;

    const lastAction = competition.last_action;
    const actionKeys = Object.keys(lastAction).filter((key) =>
        !['id', 'competition_id', 'created_at', 'updated_at', 'predictions_trained_to'].includes(key)
    );

    const handleUpdateAction = async (action: string, shouldReloadState = true) => {
        setLoadingActions((prevState) => ({ ...prevState, [action]: true }));
        setLoading(true); // Prevent other actions
    
        // Generate a random job ID
        const jobId = Math.random().toString(36).substring(2, 8);
    
        try {
            const response = await post(`dashboard/competitions/view/${competition.id}/update-action/${action}`, {
                job_id: jobId
            });
            console.log(response);
            if (shouldReloadState && getRecord) getRecord();
        } catch (error) {
            console.error(error);
        } finally {
            setLoadingActions((prevState) => ({ ...prevState, [action]: false }));
            setLoading(false);
        }
    };
    

    const handleUpdateAllActions = async () => {
        setLoading(true);

        try {
            for (const action of actionKeys) {
                await handleUpdateAction(action, false); // Wait for each action to complete
            }
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false); // Re-enable the button after all actions are completed
            if (getRecord) getRecord();
        }
    };

    type Prefix = {
        name: string
        color: string
    }

    const prefixes: Prefix[] = []

    // Predefined colors to use for prefixes
    const colors = ['blue', 'green', 'orange', 'purple', 'pink'];

    // Keep track of the last assigned color for each prefix
    const prefixColors: { [key: string]: string } = {};

    return (
        <Card>
            <CardBody>
                <Row className="d-flex justify-content-between align-items-center my-2">
                    <Col>Last actions ID: #{lastAction.id}</Col>
                    <Col className="col-5 col-md-4 col-lg-3 text-end">
                        <Button variant="success" onClick={handleUpdateAllActions} disabled={loading}>
                            {loading ? 'Updating All...' : 'Update All Actions'}
                        </Button>
                    </Col>
                </Row>
                <Table striped bordered hover>
                    <thead>
                        <tr>
                            <th>Action Type</th>
                            <th>Updated Date</th>
                            <th>Update Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {actionKeys.map((key) => {

                            const prefix = key.split('_')[0];

                            // Check if the prefix already has an assigned color
                            if (!prefixColors[prefix]) {
                                // Assign the next color from the predefined array
                                const colorIndex = Object.keys(prefixColors).length % colors.length;
                                prefixColors[prefix] = colors[colorIndex];
                            }

                            // Find the corresponding color for the prefix
                            const prefixColor = prefixColors[prefix];

                            return (
                                <tr key={key}>
                                    <td>
                                        <div style={{ borderLeft: `solid 5px ${prefixColor}`, padding: '0.5rem' }}>
                                            {(key.charAt(0).toLocaleUpperCase() + key.slice(1)).replace(/_/g, ' ')}
                                        </div>
                                    </td>
                                    <td>
                                        {lastAction[key] && typeof lastAction[key] === 'string' && !isNaN(new Date(lastAction[key]!).getTime())
                                            ? <TimeAgo datetime={new Date(lastAction[key]!)} />
                                            : 'N/A'}
                                    </td>
                                    <td>
                                        <Button
                                            variant="primary"
                                            onClick={() => handleUpdateAction(key)}
                                            disabled={loading} // Disable all buttons when any action is loading
                                        >
                                            {loadingActions[key] ? <><Spinner animation="border" size="sm" /> Updating...</> : 'Update Action'}
                                        </Button>
                                    </td>
                                </tr>
                            )
                        })}
                    </tbody>
                </Table>
            </CardBody>
        </Card>
    );
};

export default LastActions;
