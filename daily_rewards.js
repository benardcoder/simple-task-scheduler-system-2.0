const DailyReward = () => {
    const [lastClaimTime, setLastClaimTime] = useState(null);
    const [canClaim, setCanClaim] = useState(false);
    const { user, setUser } = useContext(UserContext);
  
    useEffect(() => {
      const checkRewardStatus = () => {
        const lastClaim = localStorage.getItem('lastRewardClaim');
        if (!lastClaim) {
          setCanClaim(true);
          return;
        }
  
        const timeDiff = Date.now() - parseInt(lastClaim);
        const hoursPassed = timeDiff / (1000 * 60 * 60);
        setCanClaim(hoursPassed >= 24);
        setLastClaimTime(parseInt(lastClaim));
      };
  
      checkRewardStatus();
    }, []);
  
    const claimDailyReward = async () => {
      if (!canClaim) return;
  
      const reward = Math.floor(Math.random() * (100 - 50 + 1)) + 50; // Random reward between 50-100
      
      try {
        const userRef = doc(db, 'users', user.uid);
        await updateDoc(userRef, {
          points: increment(reward)
        });
  
        setUser(prev => ({...prev, points: prev.points + reward}));
        localStorage.setItem('lastRewardClaim', Date.now().toString());
        setCanClaim(false);
        setLastClaimTime(Date.now());
        
        toast.success(`Claimed ${reward} points as daily reward!`);
      } catch (error) {
        toast.error('Failed to claim reward');
      }
    };
  
    return (
      <div className="daily-reward">
        <h2>Daily Reward</h2>
        {canClaim ? (
          <button onClick={claimDailyReward}>Claim Daily Reward</button>
        ) : (
          <div>
            <p>Next reward available in: <TimeUntilNextReward lastClaimTime={lastClaimTime} /></p>
          </div>
        )}
      </div>
    );
  };