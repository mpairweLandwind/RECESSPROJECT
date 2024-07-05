package CML_Interface;

import java.io.*;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.net.*;
import java.sql.*;
import java.util.*;
import org.mindrot.jbcrypt.BCrypt;
import org.apache.poi.ss.usermodel.*;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
//import org.apache.xmlbeans.impl.xb.ltgfmt.TestCase.Files;

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
                        writer.println("Welcome to the Competition Management System!");

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
                                case "uploadquestions":
                                    handleUploadQuestionsCommand(parts, writer, username);
                                    break;
                                case "uploadanswers":
                                    handleUploadAnswersCommand(parts, writer);
                                    break;
                                case "setchallenge":
                                    handleSetChallengeCommand(parts, writer);
                                    break;
                                case "viewreports":
                                    handleViewReportsCommand(writer);
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

    private static void handleUploadQuestionsCommand(String[] parts, PrintWriter writer, String username) {
        if (parts.length != 2) {
            writer.println("Usage: uploadQuestions <questions_file_path>");
            return;
        }

        try {
            String role = getUsernameAndRole(username);
            if (!"admin".equalsIgnoreCase(role)) {
                writer.println("You do not have permission to upload questions.");
                return;
            }
        } catch (SQLException e) {
            writer.println("Failed to check user role: " + e.getMessage());
            return;
        }

        String filePath = parts[1];
        File file = new File(filePath);

        if (!file.exists() || file.isDirectory()) {
            writer.println("Invalid file path.");
            return;
        }

        try (FileInputStream fis = new FileInputStream(file);
                Workbook workbook = new XSSFWorkbook(fis);
                Connection conn = connectToDatabase()) {
            conn.setAutoCommit(false); // Start transaction

            Sheet sheet = workbook.getSheetAt(0);
            int count = 0;
            int adminId = getUserId(username);

            if (adminId == -1) {
                writer.println("Failed to retrieve administrator ID.");
                return;
            }

            for (Row row : sheet) {
                if (row.getRowNum() == 0) {
                    // Skip header row
                    continue;
                }

                Cell IdCell = row.getCell(0);
                Cell questionTextCell = row.getCell(1);
                Cell marksCell = row.getCell(2);

                if (IdCell == null || questionTextCell == null || marksCell == null) {
                    writer.println("Invalid question format in row " + (row.getRowNum() + 1));
                    continue;
                }

                int Id = (int) IdCell.getNumericCellValue();
                String questionText = questionTextCell.getStringCellValue();
                int marks = (int) marksCell.getNumericCellValue();

                String sql = "INSERT INTO questions (id,question_text, marks, administrator_id) VALUES (?, ?, ?, ?)";
                try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                    stmt.setInt(1, Id);
                    stmt.setString(2, questionText);
                    stmt.setInt(3, marks);
                    stmt.setInt(4, adminId);
                    stmt.executeUpdate();
                    count++;
                }
            }

            conn.commit(); // Commit transaction
            writer.println("Questions uploaded successfully! Total: " + count);
        } catch (Exception e) {
            writer.println("Failed to upload questions: " + e.getMessage());
        }
    }

    private static void handleUploadAnswersCommand(String[] parts, PrintWriter writer) {
        if (parts.length != 2) {
            writer.println("Usage: uploadAnswers <answers_file_path>");
            return;
        }

        String filePath = parts[1];

        try (InputStream fileStream = new FileInputStream(filePath);
                Workbook workbook = new XSSFWorkbook(fileStream);
                Connection conn = connectToDatabase()) {

            Sheet sheet = workbook.getSheetAt(0);
            Iterator<Row> rowIterator = sheet.iterator();

            while (rowIterator.hasNext()) {
                Row row = rowIterator.next();
                if (row.getRowNum() == 0) {
                    // Skip the header row
                    continue;
                }

                Cell questionIdCell = row.getCell(0);
                Cell answerTextCell = row.getCell(1);
                Cell isCorrectCell = row.getCell(2);

                if (questionIdCell == null || answerTextCell == null || isCorrectCell == null) {
                    writer.println("Invalid answer format in file.");
                    continue;
                }

                long questionId = (long) questionIdCell.getNumericCellValue();
                String answerText = answerTextCell.getStringCellValue();
                boolean isCorrect = isCorrectCell.getBooleanCellValue();

                String sql = "INSERT INTO answers (question_id, answer_text, is_correct, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
                PreparedStatement stmt = conn.prepareStatement(sql);
                stmt.setLong(1, questionId);
                stmt.setString(2, answerText);
                stmt.setBoolean(3, isCorrect);
                stmt.executeUpdate();
            }

            writer.println("Answers uploaded successfully!");
        } catch (IOException | SQLException e) {
            writer.println("Failed to upload answers: " + e.getMessage());
        }
    }

    private static void handleSetChallengeCommand(String[] parts, PrintWriter writer) {
        if (parts.length != 6) {
            writer.println(
                    "Usage: setChallenge <title> <description> <start_date> <end_date> <duration> <num_questions>");
            return;
        }

        String title = parts[1];
        String description = parts[2];
        String startDate = parts[3];
        String endDate = parts[4];
        int duration = Integer.parseInt(parts[5]);
        int numQuestions = Integer.parseInt(parts[6]);

        try (Connection conn = connectToDatabase()) {
            String sql = "INSERT INTO challenges (title, description, start_date, end_date, duration, number_of_questions) VALUES (?, ?, ?, ?, ?, ?)";
            PreparedStatement stmt = conn.prepareStatement(sql);
            stmt.setString(1, title);
            stmt.setString(2, description);
            stmt.setTimestamp(3, Timestamp.valueOf(startDate));
            stmt.setTimestamp(4, Timestamp.valueOf(endDate));
            stmt.setInt(5, duration);
            stmt.setInt(6, numQuestions);
            stmt.executeUpdate();

            writer.println("Challenge set successfully!");
        } catch (SQLException e) {
            writer.println("Failed to set challenge: " + e.getMessage());
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

    private static String getUsernameAndRole(String username) throws SQLException {
        String role = null;
        String sql = "SELECT role FROM users WHERE username = ?";
        try (Connection conn = connectToDatabase();
                PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, username);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    role = rs.getString("role");
                }
            }
        }
        return role;
    }

    private static int getUserId(String username) throws SQLException {
        int userId = -1;
        String sql = "SELECT id FROM users WHERE username = ?";
        try (Connection conn = connectToDatabase();
                PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, username);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    userId = rs.getInt("id");
                }
            }
        }
        return userId;
    }

}
