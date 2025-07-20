import { useState, useEffect } from 'react';

const RealTimeLogs = () => {
    const [logs, setLogs] = useState<string[]>([]);

    useEffect(() => {
        // Create a WebSocket connection
        const ws = new WebSocket('ws://your-websocket-server-url');

        // When a message is received, update the log state
        ws.onmessage = (event) => {
            setLogs((prevLogs) => [...prevLogs, event.data]);
        };

        // Cleanup WebSocket on component unmount
        return () => ws.close();
    }, []);

    return (
        <div>
            <h3>Real-time Logs</h3>
            <div style={{ whiteSpace: 'pre-wrap', height: '400px', overflowY: 'scroll' }}>
                {logs.map((log, index) => (
                    <p key={index}>{log}</p>
                ))}
            </div>
        </div>
    );
};

export default RealTimeLogs;
