package CML_Interface;

public class ScoreResult {
    private int totalScore;
    private int deductedMarks;
    private long totalTimeSpent; // in milliseconds

    public ScoreResult(int totalScore, int deductedMarks, long totalTimeSpent) {
        this.totalScore = totalScore;
        this.deductedMarks = deductedMarks;
        this.totalTimeSpent = totalTimeSpent;
    }

    public int getTotalScore() {
        return totalScore;
    }

    public int getDeductedMarks() {
        return deductedMarks;
    }

    public long getTotalTimeSpent() {
        return totalTimeSpent;
    }

    public long getTotalTimeSpentInMinutes() {
        return totalTimeSpent / (1000 * 60);
    }

    public long getRemainingSeconds() {
        return (totalTimeSpent / 1000) % 60;
    }

    @Override
    public String toString() {
        long minutes = getTotalTimeSpentInMinutes();
        long seconds = getRemainingSeconds();
        return "Total Score: " + totalScore + ", Deducted Marks: " + deductedMarks + 
               ", Total Time Spent: " + minutes + " minutes " + seconds + " seconds";
    }
}
