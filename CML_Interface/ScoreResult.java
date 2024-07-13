package CML_Interface;

public class ScoreResult {
    private int totalScore;
    private int deductedMarks;

    public ScoreResult(int totalScore, int deductedMarks) {
        this.totalScore = totalScore;
        this.deductedMarks = deductedMarks;
    }

    public int getTotalScore() {
        return totalScore;
    }

    public int getDeductedMarks() {
        return deductedMarks;
    }
}
