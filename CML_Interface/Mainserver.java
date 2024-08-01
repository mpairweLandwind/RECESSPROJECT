package CML_Interface;

import java.io.*;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.net.*;
import java.sql.*;
import java.util.*;
import javax.mail.*;
import javax.mail.internet.*;
//import java.time.*;
import org.mindrot.jbcrypt.BCrypt;
import javax.mail.PasswordAuthentication;
import java.util.concurrent.*;

public class Mainserver {

    private static final ExecutorService executorService = Executors.newCachedThreadPool();
    private static String dbUsername = "alien";
    private static String dbPassword = "alien123.com";
    private static String dbUrl = "jdbc:postgresql://localhost:5432/competition_db";
    private static Map<String, Boolean> loggedInClients = new HashMap<>();
    private static final String smtpHost = "smtp.gmail.com"; // Replace with your SMTP host
    private static final String smtpUsername = "mpairwelauben375@gmail.com";
    private static final String smtpPassword = "kotd nvgo cdvx dfgo";

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
                                case "login" -> {
                                    username = handleLoginCommand(parts, writer);
                                    isLoggedIn = (username != null);
                                }
                                case "register" -> handleRegisterCommand(parts, writer);
                                case "viewchallenges" -> handleViewChallengesCommand(writer);
                                case "viewreports" -> handleViewReportsCommand(writer);
                                case "viewapplicants" -> handleViewApplicantsCommand(writer, username);
                                case "attemptchallenge" ->
                                    handleAttemptChallengeCommand(parts, username, writer, reader);
                                case "confirm" -> handleConfirmCommand(parts, writer, username);
                                default -> writer.println("Invalid command.");
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
            String sql = "INSERT INTO users (username, firstname, lastname, email, date_of_birth, school_reg_no, password, profile_photo, status) VALUES (?,?, ?, ?, ?, ?, ?, ?, ?)";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, username);
            stmt.setString(2, firstname);
            stmt.setString(3, lastname);
            stmt.setString(4, email);
            stmt.setDate(5, java.sql.Date.valueOf(date_of_birth));
            stmt.setString(6, school_reg_no);
            stmt.setString(7, hashedPassword);
            stmt.setString(8, profilePhotoPath);
            stmt.setString(9, "inactive");
            stmt.executeUpdate();
            // Retrieve the representative's email
            String repEmail = getSchoolRepresentativeEmail(conn, school_reg_no);
            if (repEmail != null) {
                sendEmailToSchoolRep(repEmail, username, firstname, lastname, email, school_reg_no);
            }

            writer.println("Registration successful!");
        } catch (SQLException e) {
            writer.println("Registration failed: " + e.getMessage());
        }
    }

    private static void sendEmailToSchoolRep(String recipientEmail, String username, String firstname, String lastname,
            String email, String schoolRegNo) {
        String from = smtpUsername;

        String host = smtpHost;

        Properties props = new Properties();
        props.put("mail.smtp.auth", "true");
        props.put("mail.smtp.starttls.enable", "true");
        props.put("mail.smtp.host", host);
        props.put("mail.smtp.port", "587");

        Session session = Session.getInstance(props, new javax.mail.Authenticator() {
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(smtpUsername, smtpPassword);
            }
        });

        try {
            Message message = new MimeMessage(session);
            message.setFrom(new InternetAddress(from));
            message.setRecipients(Message.RecipientType.TO, InternetAddress.parse(recipientEmail));
            message.setSubject("New User Registration: " + firstname + " " + lastname);
            message.setText("A new user has been registered.\n\nName: " + firstname + " " + lastname + "\nUsername: "
                    + username + "\nEmail: " + email + "\nSchool Registration Number: " + schoolRegNo);
            Transport.send(message);
            System.out.println("Email sent successfully to " + recipientEmail);
        } catch (MessagingException e) {
            e.printStackTrace();
        }
    }

    private static String getSchoolRepresentativeEmail(Connection conn, String schoolRegNo) throws SQLException {
        String sql = "SELECT email_of_representative FROM schools WHERE registration_number = ?";
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, schoolRegNo);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getString("email_of_representative");
                }
            }
        }
        return null;
    }

    private static void handleConfirmCommand(String[] parts, PrintWriter writer, String loggedInUser) {
        if (parts.length != 3) {
            writer.println("Usage: confirm <yes/no> <username>");
            return;
        }

        String confirmation = parts[1];
        String username = parts[2];

        try (Connection conn = connectToDatabase()) {
            // Check if the logged-in user is a school representative
            String repQuery = "SELECT role FROM users WHERE username = ?";
            try (PreparedStatement repStmt = conn.prepareStatement(repQuery)) {
                repStmt.setString(1, loggedInUser);
                try (ResultSet repRs = repStmt.executeQuery()) {
                    if (!repRs.next() || !repRs.getString("role").equals("representative")) {
                        writer.println("You must be a school representative to confirm participants.");
                        return;
                    }
                }
            }

            // Get the school_reg_no of the logged-in user
            String schoolQuery = "SELECT school_reg_no FROM users WHERE username = ?";
            try (PreparedStatement schoolStmt = conn.prepareStatement(schoolQuery)) {
                schoolStmt.setString(1, loggedInUser);
                try (ResultSet schoolRs = schoolStmt.executeQuery()) {
                    if (!schoolRs.next()) {
                        writer.println("Logged-in user not found.");
                        return;
                    }

                    String schoolRegNo = schoolRs.getString("school_reg_no");

                    // Get the user_id and school_reg_no of the participant
                    String participantQuery = "SELECT id, school_reg_no FROM users WHERE username = ?";
                    try (PreparedStatement participantStmt = conn.prepareStatement(participantQuery)) {
                        participantStmt.setString(1, username);
                        try (ResultSet participantRs = participantStmt.executeQuery()) {
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

                            // Get the school_id from the schools table using the participant's
                            // school_reg_no
                            String schoolIdQuery = "SELECT id FROM schools WHERE registration_number = ?";
                            try (PreparedStatement schoolIdStmt = conn.prepareStatement(schoolIdQuery)) {
                                schoolIdStmt.setString(1, participantSchoolRegNo);
                                try (ResultSet schoolIdRs = schoolIdStmt.executeQuery()) {
                                    if (!schoolIdRs.next()) {
                                        writer.println("School not found for the participant.");
                                        return;
                                    }

                                    int schoolId = schoolIdRs.getInt("id");

                                    if (confirmation.equalsIgnoreCase("yes")) {
                                        // Update participant status to active
                                        String updateQuery = "UPDATE users SET status = 'active' WHERE id = ?";
                                        try (PreparedStatement updateStmt = conn.prepareStatement(updateQuery)) {
                                            updateStmt.setInt(1, userId);
                                            updateStmt.executeUpdate();
                                        }

                                        // Insert the user into the participants table
                                        String insertParticipantQuery = "INSERT INTO participants (participant_id, school_id, challenge_id, attempts_left, total_score, completed, time_taken, created_at, updated_at) VALUES (?, ?, NULL, 3, 0, false, 0, now(), now())";
                                        try (PreparedStatement insertParticipantStmt = conn
                                                .prepareStatement(insertParticipantQuery)) {
                                            insertParticipantStmt.setInt(1, userId);
                                            insertParticipantStmt.setInt(2, schoolId); // Use school_id from schools
                                                                                       // table
                                            insertParticipantStmt.executeUpdate();
                                        }

                                        writer.println("User " + username
                                                + " confirmed successfully and added to participants.");
                                    } else if (confirmation.equalsIgnoreCase("no")) {
                                        // Move user to rejected_participants table and delete from users table
                                        String rejectQuery = "INSERT INTO rejected_participants (participant_id, username, firstname, lastname, registration_number, reason, email, date_of_birth, created_at, updated_at) "
                                                + "SELECT id, username, firstname, lastname, school_reg_no, 'Rejected by school representative', email, date_of_birth, now(), now() FROM users WHERE id = ?";
                                        try (PreparedStatement rejectStmt = conn.prepareStatement(rejectQuery)) {
                                            rejectStmt.setInt(1, userId);
                                            rejectStmt.executeUpdate();
                                        }

                                        String deleteQuery = "DELETE FROM users WHERE id = ?";
                                        try (PreparedStatement deleteStmt = conn.prepareStatement(deleteQuery)) {
                                            deleteStmt.setInt(1, userId);
                                            deleteStmt.executeUpdate();
                                    }

                                        writer.println("User " + username + " confirmation denied.");
                                    } else {
                                        writer.println("Invalid confirmation. Use 'yes' or 'no'.");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (SQLException e) {
            writer.println("Confirmation failed: " + e.getMessage());
            e.printStackTrace(); // Print the stack trace for debugging
        }
    }



    private static void handleViewChallengesCommand(PrintWriter writer) {
        try (Connection conn = connectToDatabase()) {
            // Update SQL query to select challenges where current date is between
            // start_date and end_date
            String sql = " SELECT * \n" + //
                    "FROM challenges \n" + //
                    "WHERE end_date = (SELECT MAX(end_date) FROM challenges)\n" + //
                    "   OR start_date <= NOW() AND end_date >= NOW();\n" + //
                    "";
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
                        .append(numberOfQuestions).append("     ")
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
        String sql = "SELECT challenges.title, users.firstname, users.lastname, participants.total_score " +
                "FROM challenge_attempts " +
                "JOIN participants ON challenge_attempts.participant_id = participants.participant_id " +
                "JOIN users ON participants.participant_id = users.id " +
                "JOIN challenges ON challenge_attempts.challenge_id = challenges.id";
        Statement stmt = conn.createStatement();
        ResultSet rs = stmt.executeQuery(sql);

        writer.println("Challenge Reports:");
        while (rs.next()) {
            String challengeTitle = rs.getString("title");
            String firstname = rs.getString("firstname");
            String lastname = rs.getString("lastname");
            int score = rs.getInt("total_score");

            writer.printf("Challenge: %s, Participant: %s %s, Score: %d%n", challengeTitle, firstname, lastname, score);
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

            // Get users' data from the users table where the school_reg_no matches and role
            // is 'participant'
            String sql = "SELECT username, firstname, lastname, school_reg_no " +
                    "FROM users " +
                    "WHERE school_reg_no = ? AND role = 'participant'";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, schoolRegNo);
            ResultSet rs = stmt.executeQuery();

            StringBuilder statementData = new StringBuilder();
            while (rs.next()) {
                String username = rs.getString("username");
                String firstname = rs.getString("firstname");
                String lastname = rs.getString("lastname");
                String schoolRegNoValue = rs.getString("school_reg_no");

                // Append each value along with spaces to the statementData
                statementData.append(username).append("  ")
                        .append(firstname).append("  ")
                        .append(lastname).append("  ")
                        .append(schoolRegNoValue).append("\t");
            }
            writer.println("availableapplicants " + statementData.toString());

        } catch (SQLException e) {
            writer.println("Failed to retrieve applicants: " + e.getMessage());
        }
    }


    private static void handleAttemptChallengeCommand(String[] parts, String username, PrintWriter writer,
            BufferedReader reader) {
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
            // Check if the user is a participant and retrieve the user ID
            String userQuery = "SELECT id, role FROM users WHERE username = ?";
            PreparedStatement userStmt = conn.prepareStatement(userQuery);
            userStmt.setString(1, username);
            ResultSet userRs = userStmt.executeQuery();

            if (!userRs.next() || !"participant".equalsIgnoreCase(userRs.getString("role"))) {
                writer.println("Only participants can attempt challenges.");
                return;
            }


            int userId = userRs.getInt("id");

            // Check if participant is assigned to the challenge and retrieve attempts left
            int attemptsLeft = getRemainingAttempts(conn, userId, challengeNumber);

            if (attemptsLeft <= 0) {
                writer.println("No attempts left for this challenge.");
                return;
            }

            // Check the number of attempts already made for the same challenge by the
            // participant
            String attemptsQuery = "SELECT COUNT(*) FROM challenge_attempts WHERE challenge_id = ? AND participant_id = ?";
            PreparedStatement attemptsStmt = conn.prepareStatement(attemptsQuery);
            attemptsStmt.setInt(1, challengeNumber);
            attemptsStmt.setInt(2, userId);
            ResultSet attemptsRs = attemptsStmt.executeQuery();

            if (attemptsRs.next()) {
                int attemptCount = attemptsRs.getInt(1);
                if (attemptCount >= 3) {
                    writer.println("You have already attempted this challenge 3 times. No attempts left.");
                    return;
                }
            }

            // Submit the challenge attempt asynchronously
            CountDownLatch latch = new CountDownLatch(1);

            if (!executorService.isShutdown()) {
                executorService.submit(() -> {
                    try {
                        attemptChallenge(challengeNumber, username, writer, reader);
                    } finally {
                        latch.countDown();
                    }
                });
            } else {
                writer.println("Service is currently unavailable. Please try again later.");
                latch.countDown();
            }

            try {
                latch.await(); // Wait for the challenge attempt to complete
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                writer.println("Challenge attempt was interrupted.");
            }
        } catch (SQLException e) {
            writer.println("Failed to check remaining attempts: " + e.getMessage());
        }
}

private static void attemptChallenge(int challengeNumber, String username, PrintWriter writer,
        BufferedReader reader) {
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

        // Check if participant is assigned to the challenge
        String participantQuery = "SELECT attempts_left FROM participants WHERE participant_id = ? AND (challenge_id = ? OR challenge_id IS NULL)";
        PreparedStatement participantStmt = conn.prepareStatement(participantQuery);
        participantStmt.setInt(1, userId);
        participantStmt.setInt(2, challengeNumber);
        ResultSet participantRs = participantStmt.executeQuery();

        if (!participantRs.next()) {
            writer.println(
                    "No record found for the given participant OR wait for confirmation from school representative");
            return;
        }

        int attemptsLeft = participantRs.getInt("attempts_left");

        if (attemptsLeft <= 0) {
            writer.println("No remaining attempts for this challenge.");
            return;
        }

        // Retrieve and display challenge details
        String challengeQuery = "SELECT * FROM challenges WHERE id = ?";
        PreparedStatement challengeStmt = conn.prepareStatement(challengeQuery);
        challengeStmt.setInt(1, challengeNumber);
        ResultSet challengeRs = challengeStmt.executeQuery();

    if (challengeRs.next()) {
        displayChallengeDetails(writer, challengeRs);

        List<String> questions = fetchChallengeQuestions(conn, challengeRs.getInt("number_of_questions"));

        // Send username to the client
        writer.println("username: " + username);
        writer.flush();

        startChallengeTimer(challengeNumber, username, questions, writer, challengeRs.getInt("duration"));

        collectAndSubmitAnswers(challengeNumber, username, questions, writer, reader,
                challengeRs.getInt("duration"));
    } else {
        writer.println("Challenge not found or not valid.");
    }
} catch (SQLException e) {
    writer.println("Failed to retrieve challenge: " + e.getMessage());
    writer.flush();
}
}

private static int getRemainingAttempts(Connection conn, int userId, int challengeNumber) throws SQLException {
    String checkAttemptsQuery = "SELECT attempts_left FROM participants " +
            "WHERE participant_id = ? AND (challenge_id = ? OR challenge_id IS NULL) " +
            "ORDER BY updated_at DESC LIMIT 1";
    try (PreparedStatement checkStmt = conn.prepareStatement(checkAttemptsQuery)) {
        checkStmt.setInt(1, userId);
        checkStmt.setInt(2, challengeNumber);
        try (ResultSet rs = checkStmt.executeQuery()) {
            if (rs.next()) {
                return rs.getInt("attempts_left");
            } else {
                throw new SQLException(
                        "No record found for the given participant OR wait for confirmation from school representative");
            }
        }
    }
}


private static void displayChallengeDetails(PrintWriter writer, ResultSet challengeRs) throws SQLException {
    int duration = challengeRs.getInt("duration");
    writer.println("Challenge: " + challengeRs.getString("title") + " " + "Description: "
            + challengeRs.getString("description") + " " + "Number of Questions: "
            + challengeRs.getInt("number_of_questions") + " " + "Duration: "
            + duration + " minutes");
    writer.println("You have " + duration + " minutes to complete the challenge." + " "
            + "Starting the challenge now..... press enter key to display the next question");
    writer.flush();
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
            writer.flush();
        }
    }, duration, TimeUnit.MINUTES);
}
private static void collectAndSubmitAnswers(int challengeNumber, String username, List<String> questions,
        PrintWriter writer, BufferedReader reader, int duration) throws SQLException {
    List<String> answers = new ArrayList<>();
    long startTime = System.currentTimeMillis();
    long durationMillis = duration * 60 * 1000;

    try {
        for (String question : questions) {
            long totalTimeSpent = System.currentTimeMillis() - startTime;
            long remainingTime = durationMillis - totalTimeSpent;

            if (remainingTime <= 0) {
                writer.println("Time is up! Submitting your answers now.");
                writer.flush();
                break;
            }

            writer.println("Question: " + question);
            writer.flush();
            writer.println("Enter your answer:");
            writer.flush();

            String answer = reader.readLine();
            answers.add(answer);

            totalTimeSpent = System.currentTimeMillis() - startTime;
            remainingTime = durationMillis - totalTimeSpent;

            if (remainingTime > 0) {
                long remainingMinutes = remainingTime / 60000;
                long remainingSeconds = (remainingTime / 1000) % 60;

                writer.println("Answer received. Press Enter to display the next question. " +
                        "Remaining Time: " + remainingMinutes + " minutes " + remainingSeconds + " seconds");
                writer.flush();
                reader.readLine();
            } else {
                writer.println("Time is up! Submitting your answers now.");
                writer.flush();
                break;
            }
        }

        writer.println("End of questions");
        writer.flush();

        // Submit the collected answers and total time spent to the server
        long totalTimeSpent = System.currentTimeMillis() - startTime;
        submitChallenge(challengeNumber, username, questions, answers, writer, totalTimeSpent);
        writer.println("TotalTimeSpent: " + (totalTimeSpent / 1000) + " seconds");
        writer.flush();

    } catch (IOException e) {
        handleIOException(e, writer);
    } catch (SQLException e) {
        handleSQLException(e, writer);
    }
}

private static void handleIOException(IOException e, PrintWriter writer) {
    writer.println("An error occurred while reading your input. Please try again.");
    writer.flush();
    e.printStackTrace();
}

private static void handleSQLException(SQLException e, PrintWriter writer) {
    writer.println("An error occurred while submitting your answers. Please contact support.");
    writer.flush();
    e.printStackTrace();
}

private static void submitChallenge(int challengeNumber, String username, List<String> questions,
        List<String> answers, PrintWriter writer, long totalTimeSpent) throws SQLException {
    int participantId = getParticipantIdByUsername(username);
    Long schoolId = getSchoolIdByUsername(username);

    try (Connection conn = connectToDatabase()) {
        conn.setAutoCommit(false);

        int challengeAttemptId = saveAttemptedChallenge(conn, participantId, challengeNumber, questions, answers,
                totalTimeSpent);

        ScoreResult scoreResult = calculateScoreFromAttemptedQuestions(conn, challengeAttemptId);

        updateParticipant(conn, participantId, schoolId, challengeNumber, scoreResult, totalTimeSpent);

        insertChallengeAttempt(conn, challengeNumber, participantId, scoreResult, totalTimeSpent);
        insertChallengeParticipant(conn, challengeNumber, participantId);

        conn.commit();

        writer.println("Challenge submitted successfully. Your score: " + scoreResult);
        writer.flush();

        writer.println("The challenge has been completed and submitted.");
        writer.flush();

        executorService.shutdown();
        if (!executorService.awaitTermination(1, TimeUnit.SECONDS)) {
            executorService.shutdownNow();
        }
    } catch (SQLException | InterruptedException e) {
        handleSQLException(e, writer);
    }
}

private static void insertChallengeParticipant(Connection conn, int challengeId, int participantId)
        throws SQLException {
    String getParticipantIdQuery = "SELECT id FROM participants WHERE participant_id = ?";
    int actualParticipantId;
    try (PreparedStatement getParticipantIdStmt = conn.prepareStatement(getParticipantIdQuery)) {
        getParticipantIdStmt.setInt(1, participantId);
        ResultSet rs = getParticipantIdStmt.executeQuery();
        if (rs.next()) {
            actualParticipantId = rs.getInt("id");
        } else {
            throw new SQLException("Participant ID " + participantId + " does not exist in the participants table.");
        }
    }

    String sql = "INSERT INTO challenge_participants (challenge_id, participant_id,created_at,updated_at) VALUES (?, ?,now(),now())";
    try (PreparedStatement pstmt = conn.prepareStatement(sql)) {
        pstmt.setInt(1, challengeId);
        pstmt.setInt(2, actualParticipantId);
        pstmt.executeUpdate();
    }
}

private static void insertChallengeAttempt(Connection conn, int challengeNumber, int participantId,
        ScoreResult scoreResult, long totalTimeSpent) throws SQLException {
    // Check if an entry with zero values exists
    String getParticipantIdQuery = "SELECT id FROM participants WHERE participant_id = ?";
    int actualParticipantId;
    try (PreparedStatement getParticipantIdStmt = conn.prepareStatement(getParticipantIdQuery)) {
        getParticipantIdStmt.setInt(1, participantId);
        ResultSet rs = getParticipantIdStmt.executeQuery();
        if (rs.next()) {
            actualParticipantId = rs.getInt("id");
        } else {
            throw new SQLException("Participant ID " + participantId + " does not exist in the participants table.");
        }
    }

    // Check if a challenge attempt exists with zero values
    String checkQuery = "SELECT id FROM challenge_attempts WHERE challenge_id = ? AND participant_id = ? AND score = 0 AND deducted_marks = 0 AND time_taken = 0";
    Integer existingAttemptId = null;

    try (PreparedStatement checkStmt = conn.prepareStatement(checkQuery)) {
        checkStmt.setInt(1, challengeNumber);
        checkStmt.setInt(2, actualParticipantId);
        try (ResultSet rs = checkStmt.executeQuery()) {
            if (rs.next()) {
                existingAttemptId = rs.getInt("id");
            }
        }
    }

    // Calculate the sum of question_ids in attempted_questions for the participant
    String sumQuestionsQuery = "SELECT COUNT(question_id) AS total_attempted_questions FROM attempted_questions WHERE participant_id = ?";
    int totalAttemptedQuestions;
    try (PreparedStatement sumQuestionsStmt = conn.prepareStatement(sumQuestionsQuery)) {
        sumQuestionsStmt.setInt(1, actualParticipantId);
        try (ResultSet rs = sumQuestionsStmt.executeQuery()) {
            if (rs.next()) {
                totalAttemptedQuestions = rs.getInt("total_attempted_questions");
            } else {
                throw new SQLException(
                        "Failed to retrieve the total attempted questions for participant ID " + participantId);
            }
        }
    }

    // Retrieve the number of questions in the challenge
    String getNumberOfQuestionsQuery = "SELECT number_of_questions FROM challenges WHERE id = ?";
    int numberOfQuestions;
    try (PreparedStatement getNumberOfQuestionsStmt = conn.prepareStatement(getNumberOfQuestionsQuery)) {
        getNumberOfQuestionsStmt.setInt(1, challengeNumber);
        try (ResultSet rs = getNumberOfQuestionsStmt.executeQuery()) {
            if (rs.next()) {
                numberOfQuestions = rs.getInt("number_of_questions");
            } else {
                throw new SQLException("Challenge ID " + challengeNumber + " does not exist in the challenges table.");
            }
        }
    }

    // Determine if the challenge attempt should be marked as completed
    boolean completed = totalAttemptedQuestions == numberOfQuestions;

    if (existingAttemptId != null) {
        // Update the existing entry
        String updateAttempt = "UPDATE challenge_attempts SET score = ?, deducted_marks = ?, time_taken = ?, completed = ?, updated_at = NOW() WHERE id = ?";
        try (PreparedStatement updateStmt = conn.prepareStatement(updateAttempt)) {
            updateStmt.setInt(1, scoreResult.getTotalScore());
            updateStmt.setInt(2, scoreResult.getDeductedMarks());
            updateStmt.setLong(3, totalTimeSpent);
            updateStmt.setBoolean(4, completed);
            updateStmt.setInt(5, existingAttemptId);
            updateStmt.executeUpdate();
        }
    } else {
        // Insert a new entry
        String insertAttempt = "INSERT INTO challenge_attempts (challenge_id, participant_id, score, deducted_marks, time_taken, completed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        try (PreparedStatement insertStmt = conn.prepareStatement(insertAttempt)) {
            insertStmt.setInt(1, challengeNumber);
            insertStmt.setInt(2, actualParticipantId);
            insertStmt.setInt(3, scoreResult.getTotalScore());
            insertStmt.setInt(4, scoreResult.getDeductedMarks());
            insertStmt.setLong(5, totalTimeSpent);
            insertStmt.setBoolean(6, completed);
            insertStmt.executeUpdate();
        }
    }
}
private static void updateParticipant(Connection conn, int participantId, Long schoolId, int challengeNumber,
        ScoreResult scoreResult, long totalTimeSpent) throws SQLException {
    String updateQuery1 = "UPDATE participants SET " +
            "challenge_id = ?, " +
            "attempts_left = attempts_left - 1, " +
            "total_score = total_score + ?, " +
            "time_taken = time_taken + ?, " +
            "completed = ? " +
            "WHERE participant_id = ? AND challenge_id IS NULL";

    String getLastAttemptQuery = "SELECT attempts_left FROM participants " +
            "WHERE participant_id = ? AND challenge_id = ? AND completed = true " +
            "ORDER BY updated_at DESC LIMIT 1";

    String insertQuery = "INSERT INTO participants (participant_id, school_id, challenge_id, attempts_left, total_score, completed, time_taken, created_at, updated_at) "
            + "VALUES (?, ?, ?, ?, ?, ?, ?, now(), now())";

    String countAttemptedQuestionsQuery = "SELECT COUNT(*) AS attempted_questions " +
            "FROM attempted_questions " +
            "WHERE participant_id = ? AND challenge_attempt_id = (" +
            "SELECT MAX(challenge_attempt_id) FROM attempted_questions " +
            "WHERE participant_id = ? AND challenge_id = ?)";

    String getTotalQuestionsQuery = "SELECT number_of_questions FROM challenges WHERE id = ?";

    boolean initialAutoCommit = conn.getAutoCommit(); // Save the current auto-commit state

    try {
        if (initialAutoCommit) {
            conn.setAutoCommit(false); // Start transaction
        }

        // Get the number of attempted questions by the participant for the most recent
        // challenge attempt
        int attemptedQuestions;
        try (PreparedStatement countAttemptedQuestionsStmt = conn.prepareStatement(countAttemptedQuestionsQuery)) {
            countAttemptedQuestionsStmt.setInt(1, participantId);
            countAttemptedQuestionsStmt.setInt(2, participantId);
            countAttemptedQuestionsStmt.setInt(3, challengeNumber);
            ResultSet rs = countAttemptedQuestionsStmt.executeQuery();
            rs.next();
            attemptedQuestions = rs.getInt("attempted_questions");
        }

        // Get the total number of questions in the challenge
        int totalQuestions;
        try (PreparedStatement getTotalQuestionsStmt = conn.prepareStatement(getTotalQuestionsQuery)) {
            getTotalQuestionsStmt.setInt(1, challengeNumber);
            ResultSet rs = getTotalQuestionsStmt.executeQuery();
            rs.next();
            totalQuestions = rs.getInt("number_of_questions");
        }

        boolean isCompleted = attemptedQuestions == totalQuestions;

        // First update query: update when participant_id=? and challenge_id is null
        try (PreparedStatement updateStmt1 = conn.prepareStatement(updateQuery1)) {
            updateStmt1.setInt(1, challengeNumber);
            updateStmt1.setInt(2, scoreResult.getTotalScore());
            updateStmt1.setLong(3, totalTimeSpent);
            updateStmt1.setBoolean(4, isCompleted);
            updateStmt1.setInt(5, participantId);
            int rowsUpdated1 = updateStmt1.executeUpdate();
            if (rowsUpdated1 > 0) {
                conn.commit(); // Commit transaction
                return; // Exit method after successful update
            }
        }

        // Get the most recent attempt to determine the number of attempts left
        int attemptsLeft;
        try (PreparedStatement getLastAttemptStmt = conn.prepareStatement(getLastAttemptQuery)) {
            getLastAttemptStmt.setInt(1, participantId);
            getLastAttemptStmt.setInt(2, challengeNumber);
            ResultSet rs = getLastAttemptStmt.executeQuery();
            if (rs.next()) {
                attemptsLeft = rs.getInt("attempts_left") - 1; // Decrement attempts left by 1
            } else {
                attemptsLeft = 3; // Default value if no previous attempts found
            }
        }

        // Insert a new record if no rows were updated
        try (PreparedStatement insertStmt = conn.prepareStatement(insertQuery)) {
            insertStmt.setInt(1, participantId);
            insertStmt.setLong(2, schoolId);
            insertStmt.setInt(3, challengeNumber);
            insertStmt.setInt(4, attemptsLeft); // Use the decremented attempts left
            insertStmt.setInt(5, scoreResult.getTotalScore());
            insertStmt.setBoolean(6, isCompleted); // Set completed based on question count
            insertStmt.setLong(7, totalTimeSpent);
            insertStmt.executeUpdate();
            conn.commit(); // Commit transaction
        }
    } catch (SQLException e) {
        e.printStackTrace();
        try {
            conn.rollback(); // Rollback transaction on error
        } catch (SQLException rollbackEx) {
            rollbackEx.printStackTrace();
        }
        throw e; // Re-throw the exception to signal failure
    } finally {
        try {
            if (conn != null && initialAutoCommit) {
                conn.setAutoCommit(true); // Reset auto-commit to its initial state
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
}


private static void handleSQLException(Exception e, PrintWriter writer) {
    writer.println("An error occurred: " + e.getMessage());
}

private static int saveAttemptedChallenge(Connection conn, int participantId, int challengeNumber,
        List<String> questions, List<String> userAnswers, long totalTimeSpent) throws SQLException {
    String getParticipantIdQuery = "SELECT id FROM participants WHERE participant_id = ?";
    int actualParticipantId;
    try (PreparedStatement getParticipantIdStmt = conn.prepareStatement(getParticipantIdQuery)) {
        getParticipantIdStmt.setInt(1, participantId);
        ResultSet rs = getParticipantIdStmt.executeQuery();
        if (rs.next()) {
            actualParticipantId = rs.getInt("id");
        } else {
            throw new SQLException("Participant ID " + participantId + " does not exist in the participants table.");
        }
    }

    String insertAttempt = "INSERT INTO challenge_attempts (challenge_id, participant_id, score, deducted_marks, time_taken, completed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    try (PreparedStatement insertAttemptStmt = conn.prepareStatement(insertAttempt, Statement.RETURN_GENERATED_KEYS)) {
        insertAttemptStmt.setInt(1, challengeNumber);
        insertAttemptStmt.setInt(2, actualParticipantId);
        insertAttemptStmt.setInt(3, 0);
        insertAttemptStmt.setInt(4, 0);
        insertAttemptStmt.setInt(5, 0);
        insertAttemptStmt.setBoolean(6, false);
        insertAttemptStmt.executeUpdate();

        try (ResultSet generatedKeys = insertAttemptStmt.getGeneratedKeys()) {
            if (generatedKeys.next()) {
                int challengeAttemptId = generatedKeys.getInt(1);
                saveAttemptedQuestions(conn, actualParticipantId, challengeAttemptId, questions, userAnswers, null);
                return challengeAttemptId;
            } else {
                throw new SQLException("Failed to obtain challenge attempt ID.");
            }
        }
    }
}

private static void saveAttemptedQuestions(Connection conn, int participantId, int challengeAttemptId,
        List<String> questions, List<String> userAnswers, Object object) throws SQLException {


    // Step 2: Insert attempted questions
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
            insertQuestionStmt.setBoolean(6, false);
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
        String answerQuery = "SELECT q.marks " +
                "FROM questions q " +
                "JOIN answers a ON q.id = a.question_id " +
                "WHERE q.id = ? AND a.answer_text = ? AND a.is_correct = TRUE";

        try (PreparedStatement answerStmt = conn.prepareStatement(answerQuery)) {
            answerStmt.setInt(1, questionId);
            answerStmt.setString(2, userAnswer);

            try (ResultSet answerRs = answerStmt.executeQuery()) {
                if (answerRs.next()) {
                    return answerRs.getInt("marks");
                } else {
                    return 0; // No match found
                }
            }
        }
    }

    private static ScoreResult calculateScoreFromAttemptedQuestions(Connection conn, int challengeAttemptId)
            throws SQLException {
        int totalDeductedMarks = 0;

        // Select rows with wrong answers
        String selectWrongAnswersQuery = "SELECT id FROM attempted_questions WHERE challenge_attempt_id = ? AND marks_awarded = 0 AND given_answer IS NOT NULL AND given_answer <> ''";
        try (PreparedStatement selectWrongAnswersStmt = conn.prepareStatement(selectWrongAnswersQuery)) {
            selectWrongAnswersStmt.setInt(1, challengeAttemptId);
            try (ResultSet rs = selectWrongAnswersStmt.executeQuery()) {
                List<Integer> idsToUpdate = new ArrayList<>();
                while (rs.next()) {
                    idsToUpdate.add(rs.getInt("id"));
                }

                // Update rows by subtracting 3 from marks_awarded
                if (!idsToUpdate.isEmpty()) {
                    String updateMarksQuery = "UPDATE attempted_questions SET marks_awarded = marks_awarded - 3 WHERE id = ?";
                    try (PreparedStatement updateMarksStmt = conn.prepareStatement(updateMarksQuery)) {
                        for (int id : idsToUpdate) {
                            updateMarksStmt.setInt(1, id);
                            updateMarksStmt.executeUpdate();
                            totalDeductedMarks -= 3; // Accumulate total deducted marks
                        }
                    }
                }
            }
        }

        // Calculate the total score and total time spent
        String scoreAndTimeQuery = "SELECT SUM(marks_awarded) AS total_score FROM attempted_questions WHERE challenge_attempt_id = ?";
        try (PreparedStatement scoreAndTimeStmt = conn.prepareStatement(scoreAndTimeQuery)) {
            scoreAndTimeStmt.setInt(1, challengeAttemptId);
            try (ResultSet rs = scoreAndTimeStmt.executeQuery()) {
                if (rs.next()) {
                    int totalScore = rs.getInt("total_score");


                    // Return the total score, deducted marks, and total time spent
                    return new ScoreResult(totalScore, totalDeductedMarks);
                } else {
                    throw new SQLException(
                            "Failed to retrieve scores and time spent for challenge attempt ID: " + challengeAttemptId);
                }
            }
        }
    }

private static Long getSchoolIdByUsername(String username) throws SQLException {
    Long schoolId = null;
    String sql = "SELECT s.id AS school_id " +
            "FROM users u " +
            "JOIN schools s ON u.school_reg_no = s.registration_number " +
            "WHERE u.username = ?";
        try (Connection conn = connectToDatabase();
                PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, username);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    schoolId = rs.getLong("school_id");
                }
            }
        }
        return schoolId;
    }


    // Helper method to get participant ID by username
    private static int getParticipantIdByUsername(String username) throws SQLException {
        try (Connection conn = connectToDatabase()) {
            String query = "SELECT p.participant_id FROM participants p JOIN users u ON p.participant_id = u.id WHERE u.username = ?";
            PreparedStatement stmt = conn.prepareStatement(query);
            stmt.setString(1, username);
            ResultSet rs = stmt.executeQuery();
            if (rs.next()) {
                return rs.getInt("participant_id");
            } else {
                throw new SQLException("Participant not found.");
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
