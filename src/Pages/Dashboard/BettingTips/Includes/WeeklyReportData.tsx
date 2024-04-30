import React from 'react';
import { Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  LinearScale,
  CategoryScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';
import FormatDate from '@/utils/FormatDate';

// Register required elements
ChartJS.register(
  LinearScale,
  CategoryScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

interface WeeklyReport {
  [key: string]: {
    bankroll_deposits: number;
    wins: number;
    tip_counts: number;
    betslip_counts: number;
    gains: number;
    stakes: number;
  };
}

const WeeklyGainsChart: React.FC<{ weeklyReport: WeeklyReport }> = ({ weeklyReport }) => {
  // Extracting data for chart from weekly report
  const weeks = Object.keys(weeklyReport);
  const firstWeek = FormatDate.DDMMYY(weeks[0]);
  const lastWeek = FormatDate.DDMMYY(weeks[weeks.length - 1]);
  const gainsData = weeks.map((week) => weeklyReport[week].gains);
  const winsData = weeks.map((week) => weeklyReport[week].wins);
  const depositsData = weeks.map((week) => weeklyReport[week].bankroll_deposits);
  const betslipCounts = weeks.map((week) => weeklyReport[week].betslip_counts);
  const tipsData = weeks.map((week) => weeklyReport[week].tip_counts);
  const stakesData = weeks.map((week) => weeklyReport[week].stakes);

  // Configuration options for the chart
  const options = {
    scales: {
      x: {
        type: 'category', // Specify category scale for x-axis
        title: {
          display: true,
          text: 'Week',
        },
      },
      y: {
        type: 'linear',
        beginAtZero: true,
        title: {
          display: true,
          text: 'Amount',
        },
      },
    },
  };

  const data = {
    labels: weeks,
    datasets: [
      {
        label: 'Weekly Gains',
        data: gainsData,
        fill: false,
        borderColor: 'rgba(75,192,192,1)',
        tension: 0.1,
      },
      {
        label: 'Wins',
        data: winsData,
        fill: false,
        borderColor: 'rgba(255,99,132,1)',
        tension: 0.1,
      },
      {
        label: 'Deposits',
        data: depositsData,
        fill: false,
        borderColor: '#4b36eb',
        tension: 0.1,
      },
      {
        label: 'Stakes',
        data: stakesData,
        fill: false,
        borderColor: 'rgba(54, 162, 235, 1)',
        tension: 0.1,
      },
    ],
  };


  return (
    <div className='mt-5'>
      <h5 className="mt-4 text-center fw-normal">Weekly report for the period {firstWeek} to {lastWeek}</h5>
      <Line data={data} options={options} />
      <div className="row mt-4 gap-2 gap-md-0">
        <div className="col-md-4">
          <div className="card">
            <div className="card-body">
              <h6 className="card-title fw-normal">Deposits</h6>
              <p className="card-text text-muted">
                Deposits: {depositsData.reduce((acc, val) => acc + val, 0)}
              </p>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card">
            <div className="card-body">
              <h6 className="card-title fw-normal">Wins</h6>
              <p className="card-text text-muted">Percentage: {Math.round(((winsData.reduce((acc, val) => acc + val, 0)) / (betslipCounts.reduce((acc, val) => acc + val, 0))) * 100)}%</p>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card">
            <div className="card-body">
              <h6 className="card-title fw-normal">Total Gains</h6>
              <p className="card-text text-muted">Total gains: {gainsData.reduce((acc, val) => acc + val, 0)}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default WeeklyGainsChart;
