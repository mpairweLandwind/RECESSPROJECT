package CML_Interface;

import java.io.*;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.net.*;
import java.sql.*;
import java.util.*;
import org.mindrot.jbcrypt.BCrypt;
import java.util.concurrent.*;
//import org.apache.xmlbeans.impl.xb.ltgfmt.TestCase.Files;
// import org.apache.poi.ss.usermodel.*;
// import org.apache.poi.xssf.usermodel.XSSFWorkbook;

public class Mainserver {

    private static String dbUsername = "alien";
    private static String dbPassword = "alien123.com";
    private static String dbUrl = "jdbc:postgresql://localhost:5432/competition_db";
    private static Map<String, Boolean> loggedInClients = new HashMap<>();

    public static void main(String[] args) {
        if (args.length != 2) {
            System.out.println("Usage: java Server <dbUsername> <dbPassword>");
            return;
        }

        dbUsername = args[0];
        dbPassword = args[1];

        try (ServerSocket serverSocket = new ServerSocket(8888)) {
            System.out.println("Server is running and waiting for a client...");

            while (true) {
                Socket clientSocket = serverSocket.accept();
                System.out.println("Client connected!");

                Thread thread = new Thread(() -> {
                    try (
                            OutputStream outputStream = clientSocket.getOutputStream();
                            PrintWriter writer = new PrintWriter(outputStream, true);
                            InputStream inputStream = clientSocket.getInputStream();
                            BufferedReader reader = new BufferedReader(new InputStreamReader(inputStream))) {
                        writer.println(
                                "*************  Welcome to the Competition Management System! *****************");

                        String username = null;
                        boolean isLoggedIn = false;

                        while (true) {
                            String command = reader.readLine();
                            System.out.println("Client: " + command);
                            if (command == null)
                                break; // Connection lost
                            if (command.equalsIgnoreCase("exit")) {
                                writer.println("Exiting...");
                                break;
                            }

                            if (!isLoggedIn && !command.startsWith("login ") && !command.startsWith("register ")) {
                                writer.println("Please login or register to continue.");
                                continue;
                            }

                            String[] parts = command.split(" ");
                            String commandType = parts[0];

                            switch (commandType.toLowerCase()) {
                                case "login":
                                    username = handleLoginCommand(parts, writer);
                                    isLoggedIn = (username != null);
                                    break;
                                case "register":
                                    handleRegisterCommand(parts, writer);
                                    break;
                                case "viewchallenges":
                                    handleViewChallengesCommand(writer);
                                    break;
                                case "viewreports":
                                    handleViewReportsCommand(writer);
                                    break;
                                case "viewapplicants":
                                    handleViewApplicantsCommand(writer, username);
                                    break;
                                case "attemptchallenge":
                                    handleAttemptChallengeCommand(parts, username, writer);
                                    break;
                                case "confirm":
                                    handleConfirmCommand(parts, writer, username);
                                    break;
                                default:
                                    writer.println("Invalid command.");
                                    break;
                            }
                        }
                    } catch (IOException e) {
                        System.err.println("Error handling client: " + e.getMessage());
                    } finally {
                        try {
                            clientSocket.close();
                        } catch (IOException e) {
                            System.err.println("Failed to close client socket: " + e.getMessage());
                        }
                    }
                });
                thread.start();
            }
        } catch (IOException e) {
            System.err.println("Server failed to start: " + e.getMessage());
        }
    }

    private static Connection connectToDatabase() throws SQLException {
        return DriverManager.getConnection(dbUrl, dbUsername, dbPassword);
    }

    private static String handleLoginCommand(String[] parts, PrintWriter writer) {
        if (parts.length != 3) {
            writer.println("Usage: login <username> <password>");
            return null;
        }

        String username = parts[1];
        String password = parts[2];

        try (Connection conn = connectToDatabase()) {
            String sql = "SELECT * FROM users WHERE username = ?";
            PreparedStatement statement = conn.prepareStatement(sql);
            statement.setString(1, username);

            ResultSet resultSet = statement.executeQuery();
            boolean isValid = false;
            if (resultSet.next()) {
                String hashedPassword = resultSet.getString("password");

                // Replace $2y$ with $2a$ for compatibility with jBCrypt
                if (hashedPassword.startsWith("$2y$")) {
                    hashedPassword = "$2a$" + hashedPassword.substring(4);
                }

                isValid = BCrypt.checkpw(password, hashedPassword);
            }

            resultSet.close();
            statement.close();

            if (isValid) {
                loggedInClients.put(username, true);
                writer.println("Login successful!");
                return username;
            } else {
                writer.println("Incorrect username or password.");
                return null;
            }
        } catch (SQLException e) {
            writer.println("Login failed: " + e.getMessage());
            return null;
        }
    }

    private static void handleRegisterCommand(String[] parts, PrintWriter writer) {
        if (parts.length != 9) {
            writer.println(
                    "Usage: register <username> <firstname> <lastname> <email> <dob> <school_reg_no> <image_path> <password>");
            return;
        }

        String username = parts[1];
        String firstname = parts[2];
        String lastname = parts[3];
        String email = parts[4];
        String date_of_birth = parts[5];
        String school_reg_no = parts[6];
        String imagePath = parts[7];
        String password = parts[8];

        // Hash the password using BCrypt
        String hashedPassword = BCrypt.hashpw(password, BCrypt.gensalt(12));
        if (hashedPassword.startsWith("$2a$") || hashedPassword.startsWith("$2b$")) {
            hashedPassword = "$2y$" + hashedPassword.substring(4);
        }
        String profilePhotoPath = null;

        File sourceFile = new File(imagePath);
        if (!sourceFile.exists()) {
            writer.println("Image file not found.");
            return;
        }

        Path destDir = Paths.get("/home/landwind/RecessProject/Web_Interface/public/storage/profile_photos");
        if (!Files.exists(destDir)) {
            try {
                Files.createDirectories(destDir);
            } catch (IOException e) {
                writer.println("Failed to create directory: " + e.getMessage());
                return;
            }
        }

        Path destFile = destDir.resolve(sourceFile.getName());
        if (Files.exists(destFile)) {
            // writer.println("Image already exists and will not be overwritten.");
            profilePhotoPath = "profile_photos/" + sourceFile.getName(); // Assuming you still want to register the user
        } else {
            try {
                Files.copy(sourceFile.toPath(), destFile);
                profilePhotoPath = "profile_photos/" + sourceFile.getName();
            } catch (IOException e) {
                writer.println("Failed to copy image file: " + e.getMessage());
                return;
            }
        }

        try (Connection conn = connectToDatabase()) {
            String sql = "INSERT INTO users (username, firstname, lastname, email, date_of_birth, school_reg_no, password, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, username);
            stmt.setString(2, firstname);
            stmt.setString(3, lastname);
            stmt.setString(4, email);
            stmt.setDate(5, java.sql.Date.valueOf(date_of_birth));
            stmt.setString(6, school_reg_no);
            stmt.setString(7, hashedPassword);
            stmt.setString(8, profilePhotoPath);
            stmt.executeUpdate();

            writer.println("Registration successful!");
        } catch (SQLException e) {
            writer.println("Registration failed: " + e.getMessage());
        }
    }

    private static void handleConfirmCommand(String[] parts, PrintWriter writer, String loggedInUser) {
        if (parts.length != 3) {
            writer.println("Usage: confirm <yes/no> <username>");
            return;
        }

        String confirmation = parts[1];
        String username = parts[2];

        try (Connection conn = connectToDatabase()) {
            // Get the school_id of the logged-in user
            String schoolQuery = "SELECT school_reg_no FROM users WHERE username = ?";
            PreparedStatement schoolStmt = conn.prepareStatement(schoolQuery);
            schoolStmt.setString(1, loggedInUser);
            ResultSet schoolRs = schoolStmt.executeQuery();

            if (!schoolRs.next()) {
                writer.println("Logged-in user not found.");
                return;
            }

            String schoolRegNo = schoolRs.getString("school_reg_no");

            // Get the user_id and school_id of the participant
            String participantQuery = "SELECT id, school_reg_no FROM users WHERE username = ?";
            PreparedStatement participantStmt = conn.prepareStatement(participantQuery);
            participantStmt.setString(1, username);
            ResultSet participantRs = participantStmt.executeQuery();

            if (!participantRs.next()) {
                writer.println("User not found.");
                return;
            }

            int userId = participantRs.getInt("id");
            String participantSchoolRegNo = participantRs.getString("school_reg_no");

            if (!schoolRegNo.equals(participantSchoolRegNo)) {
                writer.println("You can only confirm participants from your school.");
                return;
            }

            if (confirmation.equalsIgnoreCase("yes")) {
                // Update participant status to confirmed
                String updateQuery = "UPDATE participants SET confirmed = true WHERE user_id = ?";
                PreparedStatement updateStmt = conn.prepareStatement(updateQuery);
                updateStmt.setInt(1, userId);
                updateStmt.executeUpdate();
                writer.println("User " + username + " confirmed successfully.");
            } else if (confirmation.equalsIgnoreCase("no")) {
                // Move user to rejected_participants table and delete from users table
                String rejectQuery = "INSERT INTO rejected_participants (user_id, username, firstname, lastname, school_id, reason, email, date_of_birth, created_at, updated_at) "
                        + "SELECT id, username, firstname, lastname, school_reg_no, 'Rejected by school representative', email, date_of_birth, now(), now() FROM users WHERE id = ?";
                PreparedStatement rejectStmt = conn.prepareStatement(rejectQuery);
                rejectStmt.setInt(1, userId);
                rejectStmt.executeUpdate();

                String deleteQuery = "DELETE FROM users WHERE id = ?";
                PreparedStatement deleteStmt = conn.prepareStatement(deleteQuery);
                deleteStmt.setInt(1, userId);
                deleteStmt.executeUpdate();

                writer.println("User " + username + " confirmation denied.");
            } else {
                writer.println("Invalid confirmation. Use 'yes' or 'no'.");
            }
        } catch (SQLException e) {
            writer.println("Confirmation failed: " + e.getMessage());
        }
    }


    private static void handleViewChallengesCommand(PrintWriter writer) {
        try (Connection conn = connectToDatabase()) {
            String sql = "SELECT * FROM challenges WHERE challenges.status = 'invalid'";
            Statement stmt = conn.createStatement();
            ResultSet rs = stmt.executeQuery(sql);

            StringBuilder statementData = new StringBuilder();
            while (rs.next()) {
                int id = rs.getInt("id");
                String title = rs.getString("title");
                String description = rs.getString("description");
                Timestamp startDate = rs.getTimestamp("start_date");
                Timestamp endDate = rs.getTimestamp("end_date");
                int duration = rs.getInt("duration");
                int numberOfQuestions = rs.getInt("number_of_questions");
                String status = rs.getString("status");

                // Append each value along with spaces to the statementData
                statementData.append(" ").append(id).append("  ")
                        .append(title).append("  ")
                        .append(description).append("  ")
                        .append(status).append("  ")
                        .append(numberOfQuestions).append("    ")
                        .append(startDate).append("AM ")
                        .append(endDate).append("PM ")
                        .append(duration).append("Mins").append("\t");
            }
            writer.println("availablechallenges " + statementData.toString());

            rs.close();
            stmt.close();
            conn.close();
        } catch (SQLException e) {
            writer.println("Failed to retrieve challenges: " + e.getMessage());
        }
    }

    private static void handleViewReportsCommand(PrintWriter writer) {
        try (Connection conn = connectToDatabase()) {
            String sql = "SELECT challenges.title, users.firstname, users.lastname, attempts.score " +
                    "FROM attempts " +
                    "JOIN participants ON attempts.participant_id = participants.id " +
                    "JOIN users ON participants.user_id = users.id " +
                    "JOIN challenges ON attempts.challenge_id = challenges.id";
            Statement stmt = conn.createStatement();
            ResultSet rs = stmt.executeQuery(sql);

            writer.println("Challenge Reports:");
            while (rs.next()) {
                String challengeTitle = rs.getString("title");
                String firstname = rs.getString("firstname");
                String lastname = rs.getString("lastname");
                int score = rs.getInt("score");

                writer.printf("Challenge: %s, Participant: %s %s, Score: %d%n", challengeTitle, firstname, lastname,
                        score);
            }
        } catch (SQLException e) {
            writer.println("Failed to view reports: " + e.getMessage());
        }
    }

    private static void handleViewApplicantsCommand(PrintWriter writer, String loggedInUser) {
        try (Connection conn = connectToDatabase()) {
            // Check if the logged-in user is a representative and get their school
            // registration number
            String repQuery = "SELECT school_reg_no FROM users WHERE username = ? AND role = 'representative'";
            PreparedStatement repStmt = conn.prepareStatement(repQuery);
            repStmt.setString(1, loggedInUser);
            ResultSet repRs = repStmt.executeQuery();

            if (!repRs.next()) {
                writer.println("Logged-in user is not a school representative.");
                return;
            }

            String schoolRegNo = repRs.getString("school_reg_no");

            // Get participants' data from the participants table where the school_reg_no
            // matches
            String sql = "SELECT u.username, u.firstname, u.lastname " +
                    "FROM users u " +
                    "JOIN participants p ON u.id = p.user_id " +
                    "JOIN schools s ON p.school_id = s.id " +
                    "WHERE s.registration_number = ?";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, schoolRegNo);
            ResultSet rs = stmt.executeQuery();

            writer.println("Applicants:");

            while (rs.next()) {
                String username = rs.getString("username");
                String firstname = rs.getString("firstname");
                String lastname = rs.getString("lastname");

                writer.printf("Username: %s, Firstname: %s, Lastname: %s%n", username, firstname, lastname);
            }

        } catch (SQLException e) {
            writer.println("Failed to retrieve applicants: " + e.getMessage());
        }
    }

    private static void handleAttemptChallengeCommand(String[] parts, String username, PrintWriter writer) {
        if (parts.length != 2) {
            writer.println("Usage: attemptChallenge <challengeNumber>");
            return;
        }

        int challengeNumber;
        try {
            challengeNumber = Integer.parseInt(parts[1]);
        } catch (NumberFormatException e) {
            writer.println("Invalid challenge number.");
            return;
        }

        try (Connection conn = connectToDatabase()) {
            // Check if the user is a participant
            String userQuery = "SELECT id, role FROM users WHERE username = ?";
            PreparedStatement userStmt = conn.prepareStatement(userQuery);
            userStmt.setString(1, username);
            ResultSet userRs = userStmt.executeQuery();

            if (!userRs.next() || !"participant".equalsIgnoreCase(userRs.getString("role"))) {
                writer.println("Only participants can attempt challenges.");
                return;
            }

            int userId = userRs.getInt("id");

            // Check remaining attempts
            int attemptsLeft = getRemainingAttempts(conn, userId, challengeNumber);
            if (attemptsLeft <= 0) {
                writer.println("No attempts left for this challenge.");
                return;
            }

            // Retrieve and display challenge details
            String challengeQuery = "SELECT * FROM challenges WHERE id = ? AND status = 'invalid'";
            PreparedStatement challengeStmt = conn.prepareStatement(challengeQuery);
            challengeStmt.setInt(1, challengeNumber);
            ResultSet challengeRs = challengeStmt.executeQuery();

            if (challengeRs.next()) {
                displayChallengeDetails(writer, challengeRs);

                List<String> questions = fetchChallengeQuestions(conn, challengeRs.getInt("number_of_questions"));

                startChallengeTimer(challengeNumber, username, questions, writer, challengeRs.getInt("duration"));

                collectAndSubmitAnswers(challengeNumber, username, questions, writer, challengeRs.getInt("duration"));

            } else {
                writer.println("Challenge not found or not valid.");
            }

        } catch (SQLException e) {
            writer.println("Failed to retrieve challenge: " + e.getMessage());
        }
    }

    private static int getRemainingAttempts(Connection conn, int userId, int challengeNumber) throws SQLException {
        String checkAttemptsQuery = "SELECT attempts_left FROM participants WHERE user_id = ? AND challenge_id = ?";
        PreparedStatement checkStmt = conn.prepareStatement(checkAttemptsQuery);
        checkStmt.setInt(1, userId);
        checkStmt.setInt(2, challengeNumber);
        ResultSet rs = checkStmt.executeQuery();
        return rs.next() ? rs.getInt("attempts_left") : 3;
    }

    private static void displayChallengeDetails(PrintWriter writer, ResultSet challengeRs) throws SQLException {
        writer.println("Challenge: " + challengeRs.getString("title")+"Description: " + challengeRs.getString("description")+"Duration: " + challengeRs.getInt("duration") + " minutes");
        writer.println();    
        writer.println("Number of Questions: " + challengeRs.getInt("number_of_questions"));
        writer.println("You have " + challengeRs.getInt("duration") + " minutes to complete the challenge.");
        writer.println("Starting the challenge now...");
    }

    private static List<String> fetchChallengeQuestions(Connection conn, int numberOfQuestions) throws SQLException {
        List<String> questions = new ArrayList<>();
        String questionQuery = "SELECT question_text FROM questions ORDER BY RANDOM() LIMIT ?";
        PreparedStatement questionStmt = conn.prepareStatement(questionQuery);
        questionStmt.setInt(1, numberOfQuestions);
        ResultSet questionRs = questionStmt.executeQuery();

        while (questionRs.next()) {
            questions.add(questionRs.getString("question_text"));
        }

        return questions;
    }

    private static void startChallengeTimer(int challengeNumber, String username, List<String> questions,
            PrintWriter writer, int duration) {
        ScheduledExecutorService scheduler = Executors.newScheduledThreadPool(1);
        scheduler.schedule(() -> {
            try {
                submitChallenge(challengeNumber, username, questions, new ArrayList<>(), writer, duration);
            } catch (SQLException e) {
                writer.println("Failed to submit the challenge: " + e.getMessage());
            }
        }, duration, TimeUnit.MINUTES);
    }

    private static void collectAndSubmitAnswers(int challengeNumber, String username, List<String> questions,
            PrintWriter writer, int duration) {
        new Thread(() -> {
            try (BufferedReader clientInput = new BufferedReader(new InputStreamReader(System.in))) {
                List<String> answers = new ArrayList<>();
                for (String question : questions) {
                    writer.println("Question: " + question);
                    writer.println("Enter your answer:");
                    String answer = clientInput.readLine();
                    answers.add(answer);
                }
                submitChallenge(challengeNumber, username, questions, answers, writer, duration);
            } catch (IOException | SQLException e) {
                writer.println("Error during challenge attempt: " + e.getMessage());
            }
        }).start();
    }

    private static void submitChallenge(int challengeNumber, String username, List<String> questions,
            List<String> userAnswers, PrintWriter writer, int duration) throws SQLException {
        int participantId = getParticipantIdByUsername(username);
        String schoolId = getSchoolIdByUsername(username);

        try (Connection conn = connectToDatabase()) {
            conn.setAutoCommit(false);

            int challengeAttemptId = saveAttemptedChallenge(conn, participantId, challengeNumber, questions,
                    userAnswers);

            int score = calculateScoreFromAttemptedQuestions(conn, challengeAttemptId);

            insertChallengeAttempt(conn, challengeNumber, participantId, score, duration);

            updateParticipant(conn, participantId, schoolId, challengeNumber, score, duration);

            conn.commit();

            writer.println("Challenge submitted successfully. Your score: " + score);
        } catch (SQLException e) {
            handleSQLException(e, writer);
        }
    }

    private static void insertChallengeAttempt(Connection conn, int challengeNumber, int participantId, int score,
            int duration) throws SQLException {
        String insertAttempt = "INSERT INTO challenge_attempts (challenge_id, participant_id, score, time_taken, completed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        try (PreparedStatement insertStmt = conn.prepareStatement(insertAttempt)) {
            insertStmt.setInt(1, challengeNumber);
            insertStmt.setInt(2, participantId);
            insertStmt.setInt(3, score);
            insertStmt.setInt(4, duration);
            insertStmt.setBoolean(5, true);
            insertStmt.executeUpdate();
        }
    }

    private static void updateParticipant(Connection conn, int participantId, String schoolId, int challengeNumber,
            int score, int duration) throws SQLException {
        String updateParticipantQuery = "INSERT INTO participants (user_id, school_id, challenge_id, attempts_left, total_score, time_taken) "
                +
                "VALUES (?, ?, ?, ?, ?, ?) " +
                "ON DUPLICATE KEY UPDATE " +
                "attempts_left = attempts_left - 1, " +
                "total_score = total_score + VALUES(total_score), " +
                "time_taken = time_taken + VALUES(time_taken)";
        try (PreparedStatement updateParticipantStmt = conn.prepareStatement(updateParticipantQuery)) {
            updateParticipantStmt.setInt(1, participantId);
            updateParticipantStmt.setString(2, schoolId);
            updateParticipantStmt.setInt(3, challengeNumber);
            updateParticipantStmt.setInt(4, getRemainingAttempts(conn, participantId, challengeNumber) - 1);
            updateParticipantStmt.setInt(5, score);
            updateParticipantStmt.setInt(6, duration);
            updateParticipantStmt.executeUpdate();
        }
    }

    private static void handleSQLException(SQLException e, PrintWriter writer) {
        writer.println("An error occurred: " + e.getMessage());
    }

    private static int saveAttemptedChallenge(Connection conn, int participantId, int challengeNumber,
            List<String> questions, List<String> userAnswers) throws SQLException {
        String insertAttempt = "INSERT INTO challenge_attempts (challenge_id, participant_id, score, time_taken, completed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        try (PreparedStatement insertAttemptStmt = conn.prepareStatement(insertAttempt,
                Statement.RETURN_GENERATED_KEYS)) {
            insertAttemptStmt.setInt(1, challengeNumber);
            insertAttemptStmt.setInt(2, participantId);
            insertAttemptStmt.setInt(3, 0); // Temporary score
            insertAttemptStmt.setInt(4, 0); // Temporary duration
            insertAttemptStmt.setBoolean(5, false); // Initially not completed
            insertAttemptStmt.executeUpdate();

            try (ResultSet generatedKeys = insertAttemptStmt.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    int challengeAttemptId = generatedKeys.getInt(1);
                    saveAttemptedQuestions(conn, participantId, challengeAttemptId, questions, userAnswers);
                    return challengeAttemptId;
                } else {
                    throw new SQLException("Failed to obtain challenge attempt ID.");
                }
            }
        }
    }

    private static void saveAttemptedQuestions(Connection conn, int participantId, int challengeAttemptId,
            List<String> questions, List<String> userAnswers) throws SQLException {
        String insertQuestion = "INSERT INTO attempted_questions (participant_id, challenge_attempt_id, question_id, given_answer, marks_awarded, is_repeated, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        try (PreparedStatement insertQuestionStmt = conn.prepareStatement(insertQuestion)) {
            for (int i = 0; i < questions.size(); i++) {
                int questionId = getQuestionIdByText(conn, questions.get(i));
                int marksAwarded = getMarksAwarded(conn, questionId, userAnswers.get(i));
                insertQuestionStmt.setInt(1, participantId);
                insertQuestionStmt.setInt(2, challengeAttemptId);
                insertQuestionStmt.setInt(3, questionId);
                insertQuestionStmt.setString(4, userAnswers.get(i));
                insertQuestionStmt.setInt(5, marksAwarded);
                insertQuestionStmt.setBoolean(6, false); // Default to false for is_repeated
                insertQuestionStmt.addBatch();
            }
            insertQuestionStmt.executeBatch();
        }
    }

    private static int getQuestionIdByText(Connection conn, String questionText) throws SQLException {
        String questionQuery = "SELECT id FROM questions WHERE question_text = ?";
        try (PreparedStatement questionStmt = conn.prepareStatement(questionQuery)) {
            questionStmt.setString(1, questionText);
            try (ResultSet questionRs = questionStmt.executeQuery()) {
                if (questionRs.next()) {
                    return questionRs.getInt("id");
                } else {
                    throw new SQLException("Question not found: " + questionText);
                }
            }
        }
    }

    private static int getMarksAwarded(Connection conn, int questionId, String userAnswer) throws SQLException {
        String answerQuery = "SELECT marks FROM answers WHERE question_id = ? AND answer_text = ? AND is_correct = TRUE";
        try (PreparedStatement answerStmt = conn.prepareStatement(answerQuery)) {
            answerStmt.setInt(1, questionId);
            answerStmt.setString(2, userAnswer);
            try (ResultSet answerRs = answerStmt.executeQuery()) {
                return answerRs.next() ? answerRs.getInt("marks") : 0;
            }
        }
    }

    private static int calculateScoreFromAttemptedQuestions(Connection conn, int challengeAttemptId)
            throws SQLException {
        String scoreQuery = "SELECT SUM(marks_awarded) AS total_score FROM attempted_questions WHERE challenge_attempt_id = ?";
        try (PreparedStatement scoreStmt = conn.prepareStatement(scoreQuery)) {
            scoreStmt.setInt(1, challengeAttemptId);
            try (ResultSet rs = scoreStmt.executeQuery()) {
                return rs.next() ? rs.getInt("total_score") : 0;
            }
        }
    }

    private static String getSchoolIdByUsername(String username) throws SQLException {
        String schoolId = null;
        String sql = "SELECT school_reg_no FROM users WHERE username = ?";
        try (Connection conn = connectToDatabase();
                PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, username);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    schoolId = rs.getString("school_reg_no");
                }
            }
        }
        return schoolId;
    }

    // Helper method to get participant ID by username
    private static int getParticipantIdByUsername(String username) throws SQLException {
        try (Connection conn = connectToDatabase()) {
            String query = "SELECT id FROM users WHERE username = ?";
            PreparedStatement stmt = conn.prepareStatement(query);
            stmt.setString(1, username);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getInt("id");
            } else {
                throw new SQLException("User not found.");
            }
        }
    }

}

// private static void handleUploadQuestionsCommand(String[] parts, PrintWriter
// writer, String username) {
// if (parts.length != 2) {
// writer.println("Usage: uploadQuestions <questions_file_path>");
// return;
// }

// try {
// String role = getUsernameAndRole(username);
// if (!"admin".equalsIgnoreCase(role)) {
// writer.println("You do not have permission to upload questions.");
// return;
// }
// } catch (SQLException e) {
// writer.println("Failed to check user role: " + e.getMessage());
// return;
// }

// String filePath = parts[1];
// File file = new File(filePath);

// if (!file.exists() || file.isDirectory()) {
// writer.println("Invalid file path.");
// return;
// }

// try (FileInputStream fis = new FileInputStream(file);
// Workbook workbook = new XSSFWorkbook(fis);
// Connection conn = connectToDatabase()) {
// conn.setAutoCommit(false); // Start transaction

// Sheet sheet = workbook.getSheetAt(0);
// int count = 0;
// int adminId = getUserId(username);

// if (adminId == -1) {
// writer.println("Failed to retrieve administrator ID.");
// return;
// }

// for (Row row : sheet) {
// if (row.getRowNum() == 0) {
// // Skip header row
// continue;
// }

// Cell IdCell = row.getCell(0);
// Cell questionTextCell = row.getCell(1);
// Cell marksCell = row.getCell(2);

// if (IdCell == null || questionTextCell == null || marksCell == null) {
// writer.println("Invalid question format in row " + (row.getRowNum() + 1));
// continue;
// }

// int Id = (int) IdCell.getNumericCellValue();
// String questionText = questionTextCell.getStringCellValue();
// int marks = (int) marksCell.getNumericCellValue();

// String sql = "INSERT INTO questions (id,question_text, marks,
// administrator_id) VALUES (?, ?, ?, ?)";
// try (PreparedStatement stmt = conn.prepareStatement(sql)) {
// stmt.setInt(1, Id);
// stmt.setString(2, questionText);
// stmt.setInt(3, marks);
// stmt.setInt(4, adminId);
// stmt.executeUpdate();
// count++;
// }
// }

// conn.commit(); // Commit transaction
// writer.println("Questions uploaded successfully! Total: " + count);
// } catch (Exception e) {
// writer.println("Failed to upload questions: " + e.getMessage());
// }
// }

// private static void handleUploadAnswersCommand(String[] parts, PrintWriter
// writer) {
// if (parts.length != 2) {
// writer.println("Usage: uploadAnswers <answers_file_path>");
// return;
// }

// String filePath = parts[1];

// try (InputStream fileStream = new FileInputStream(filePath);
// Workbook workbook = new XSSFWorkbook(fileStream);
// Connection conn = connectToDatabase()) {

// Sheet sheet = workbook.getSheetAt(0);
// Iterator<Row> rowIterator = sheet.iterator();

// while (rowIterator.hasNext()) {
// Row row = rowIterator.next();
// if (row.getRowNum() == 0) {
// // Skip the header row
// continue;
// }

// Cell questionIdCell = row.getCell(0);
// Cell answerTextCell = row.getCell(1);
// Cell isCorrectCell = row.getCell(2);

// if (questionIdCell == null || answerTextCell == null || isCorrectCell ==
// null) {
// writer.println("Invalid answer format in file.");
// continue;
// }

// long questionId = (long) questionIdCell.getNumericCellValue();
// String answerText = answerTextCell.getStringCellValue();
// boolean isCorrect = isCorrectCell.getBooleanCellValue();

// String sql = "INSERT INTO answers (question_id, answer_text, is_correct,
// created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
// PreparedStatement stmt = conn.prepareStatement(sql);
// stmt.setLong(1, questionId);
// stmt.setString(2, answerText);
// stmt.setBoolean(3, isCorrect);
// stmt.executeUpdate();
// }

// writer.println("Answers uploaded successfully!");
// } catch (IOException | SQLException e) {
// writer.println("Failed to upload answers: " + e.getMessage());
// }
// }

// private static int getUserId(String username) throws SQLException {
// int userId = -1;
// String sql = "SELECT id FROM users WHERE username = ?";
// try (Connection conn = connectToDatabase();
// PreparedStatement stmt = conn.prepareStatement(sql)) {
// stmt.setString(1, username);
// try (ResultSet rs = stmt.executeQuery()) {
// if (rs.next()) {
// userId = rs.getInt("id");
// }
// }
// }
// return userId;
// }
