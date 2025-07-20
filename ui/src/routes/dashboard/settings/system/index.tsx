
import joblogs from '@/routes/dashboard/settings/system/job-logs/index';
import predictionsPerformance from '@/routes/dashboard/settings/system/predictions-performance/index';

const index = [

  {
    path: 'job-logs',
    children: joblogs,
  },
  {
    path: 'predictions-performance',
    children: predictionsPerformance,
  },

]

export default index